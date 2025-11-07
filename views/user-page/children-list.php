<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}


// Get user information from session
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? 'User';
$lname = $_SESSION['lname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone_number'] ?? '';
$noprofile = $_SESSION['profileimg'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $_SESSION['fname'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Children</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="../../css/main.css" />
    <link rel="stylesheet" href="../../css/header.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/notification-style.css" />
    <link rel="stylesheet" href="../../css/user/children-list.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main>
        <section class="section-container">
            <h2 class="dashboard section-title">
                <span class="material-symbols-rounded">dashboard</span>
                My Children
            </h2>
        </section>

        <section class="children-list-section">
            <div class="filters-header">
                <span class="material-symbols-rounded" aria-hidden="true">tune</span>
                <span>Filters:</span>
            </div>
            <div class="content">
                <div class="filters">
                    <div class="select-with-icon">
                        <select id="chrFilter" aria-label="CHR Status">
                            <option value="" disabled selected>CHR Status</option>
                            <option value="all">All Children</option>
                            <option value="pending">Pending Registration</option>
                            <option value="approved" selected>Approved Children</option>
                        </select>
                        <span class="material-symbols-rounded" aria-hidden="true">filter_list</span>
                    </div>
                </div>
                <div id="childrenContainer" class="table-container">
                    <table class="table" aria-busy="true">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Upcoming</th>
                                <th>Missed</th>
                                <th>Taken</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-loading-row">
                                <td colspan="7">
                                    <div class="table-loading">
                                        <span class="table-spinner" aria-hidden="true"></span>
                                        <p>Loading children data...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
    <script>
        let allChildrenData = [];
        let allChrStatusData = [];
        let currentSort = { key: 'name', dir: 'asc' };

        document.addEventListener('DOMContentLoaded', async function() {
            const container = document.getElementById('childrenContainer');
            const filterSelect = document.getElementById('chrFilter');

            // Ensure initial table with loading row is present (already in HTML)
            if (!container.querySelector('table')) {
                container.innerHTML = `
                <table class="table" aria-busy="true">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Upcoming</th>
                            <th>Missed</th>
                            <th>Taken</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-loading-row">
                            <td colspan="7">
                                <div class="table-loading">
                                    <span class="table-spinner" aria-hidden="true"></span>
                                    <p>Loading children data...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>`;
            }

            // Load children data and CHR status data
            await Promise.all([loadChildrenData(), loadChrStatusData()]);

            // Render the table
            renderChildrenTable();

            // Add filter event listener
            filterSelect.addEventListener('change', function() {
                renderChildrenTable();
            });
        });

        async function loadChildrenData() {
            try {
                // Load all children data (not just accepted ones) for proper filtering
                const res = await fetch('../../php/supabase/users/get_accepted_child.php');
                const data = await res.json();
                allChildrenData = (data && data.status === 'success' && Array.isArray(data.data)) ? data.data : [];
            } catch (err) {
                console.error('Error loading children data:', err);
                allChildrenData = [];
            }
        }

        async function loadChrStatusData() {
            try {
                const res = await fetch('../../php/supabase/users/get_child_list.php');
                const data = await res.json();
                allChrStatusData = (data && data.status === 'success' && Array.isArray(data.data)) ? data.data : [];
            } catch (err) {
                console.error('Error loading CHR status data:', err);
                allChrStatusData = [];
            }
        }

        function renderChildrenTable() {
            const container = document.getElementById('childrenContainer');
            const filterSelect = document.getElementById('chrFilter');
            const selectedFilter = filterSelect.value;

            if (allChildrenData.length === 0) {
                container.innerHTML = '<div class="no-data">No children found</div>';
                return;
            }

            // Filter children based on selected filter
            let filteredChildren = allChildrenData.filter(child => {
                const babyId = child.baby_id || child.id || '';
                const childStatus = child.status; // This is the child registration status
                const chrStatus = getChrStatusForChild(babyId);

                switch (selectedFilter) {
                    case 'pending':
                        return childStatus === 'pending'; // Child registration is pending BHW approval
                    case 'approved':
                        return childStatus === 'accepted'; // Child is approved by BHW
                    case 'all':
                    default:
                        return true; // Show all children
                }
            });

            // Sort children based on currentSort
            const sortKey = currentSort.key;
            const sortDir = currentSort.dir === 'asc' ? 1 : -1;
            filteredChildren.sort((a, b) => {
                const va = getSortValue(a, sortKey);
                const vb = getSortValue(b, sortKey);
                if (va == null && vb == null) return 0;
                if (va == null) return 1; // nulls last
                if (vb == null) return -1;
                if (typeof va === 'number' && typeof vb === 'number') {
                    return (va - vb) * sortDir;
                }
                return String(va).localeCompare(String(vb)) * sortDir;
            });

            if (filteredChildren.length === 0) {
                container.innerHTML = `<div class="no-data">No children found for "${filterSelect.options[filterSelect.selectedIndex].text}"</div>`;
                return;
            }

            let html = '';
            html += '<table class="table" aria-busy="false">';
            html += '<thead><tr>' +
                renderHeaderCell('name', 'Name') +
                renderHeaderCell('age', 'Age') +
                renderHeaderCell('gender', 'Gender') +
                renderHeaderCell('upcoming', 'Upcoming') +
                renderHeaderCell('missed', 'Missed') +
                renderHeaderCell('taken', 'Taken') +
                '<th scope="col">Action</th>' +
                '</tr></thead>';
            html += '<tbody>';

            filteredChildren.forEach(child => {
                const fullName = (child.name) || [child.child_fname || '', child.child_lname || ''].filter(Boolean).join(' ');
                const ageText = (child.age && child.age > 0) ? (child.age + ' years') : (child.weeks_old != null ? (child.weeks_old + ' weeks') : '');
                const gender = child.gender || '';
                const babyId = child.baby_id || child.id || '';
                const upc = child.scheduled_count || 0;
                const mis = child.missed_count || 0;
                const tak = child.taken_count || 0;

                html += '<tr>' +
                    `<td>${fullName}</td>` +
                    `<td>${ageText}</td>` +
                    `<td>${gender}</td>` +
                    `<td>${upc}</td>` +
                    `<td>${mis}</td>` +
                    `<td>${tak}</td>` +
                    '<td class="actions-cell">' +
                    (babyId ? `<a class="view-btn" href="child-health-record.php?baby_id=${encodeURIComponent(String(babyId))}"><span class="material-symbols-rounded">visibility</span> View</a>` : '<span class="text-muted">N/A</span>') +
                    '</td>' +
                    '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            // Attach sort handlers and ensure aria-busy updated
            const table = container.querySelector('table');
            if (table) table.setAttribute('aria-busy', 'false');
            attachSortHandlers();
        }

        function renderHeaderCell(key, label) {
            const cls = ['sortable'];
            let aria = 'none';
            if (currentSort.key === key) {
                if (currentSort.dir === 'asc') {
                    cls.push('sort-asc');
                    aria = 'ascending';
                } else {
                    cls.push('sort-desc');
                    aria = 'descending';
                }
            }
            return `<th scope="col" class="${cls.join(' ')}" data-sort-key="${key}" role="button" tabindex="0" aria-sort="${aria}">${label}</th>`;
        }

        function attachSortHandlers() {
            const container = document.getElementById('childrenContainer');
            const headers = container.querySelectorAll('th.sortable');
            headers.forEach(th => {
                const key = th.getAttribute('data-sort-key');
                const toggleSort = () => {
                    if (currentSort.key === key) {
                        currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.key = key;
                        currentSort.dir = 'asc';
                    }
                    renderChildrenTable();
                };
                th.addEventListener('click', toggleSort);
                th.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleSort();
                    }
                });
            });
        }

        function computeAgeValue(child) {
            // Return age in days for consistent numeric sorting
            const years = Number(child.age);
            if (!isNaN(years) && years > 0) return Math.round(years * 365);
            const weeks = Number(child.weeks_old);
            if (!isNaN(weeks) && weeks >= 0) return Math.round(weeks * 7);
            return 0;
        }

        function getSortValue(child, key) {
            switch (key) {
                case 'name':
                    return (child.name) || [child.child_fname || '', child.child_lname || ''].filter(Boolean).join(' ').toLowerCase();
                case 'age':
                    return computeAgeValue(child);
                case 'gender':
                    return (child.gender || '').toLowerCase();
                case 'upcoming':
                    return Number(child.scheduled_count) || 0;
                case 'missed':
                    return Number(child.missed_count) || 0;
                case 'taken':
                    return Number(child.taken_count) || 0;
                default:
                    return '';
            }
        }

        function getChrStatusForChild(babyId) {
            const chrData = allChrStatusData.find(item => item.baby_id === babyId);
            return chrData ? chrData.chr_status : 'none';
        }

        function getChrStatusText(status) {
            switch (status) {
                case 'pending':
                    return 'Pending Request';
                case 'approved':
                    return 'Approved';
                case 'new_records':
                    return 'New Records Available';
                case 'none':
                default:
                    return 'No Request';
            }
        }

        function getChrStatusColor(status) {
            switch (status) {
                case 'pending':
                    return '#ffc107'; // Yellow
                case 'approved':
                    return '#28a745'; // Green
                case 'new_records':
                    return '#007bff'; // Blue
                case 'none':
                default:
                    return '#6c757d'; // Gray
            }
        }
    </script>
</body>

</html>