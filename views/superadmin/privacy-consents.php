<?php
session_start();
$current_page = 'privacy-consents';
$page_title = 'Privacy Consents';
$page_js = 'privacy-consents.js?v=1.0.0';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Privacy Consents</h1>
        <p>Monitor Data Privacy Act acknowledgements captured during parent onboarding.</p>
    </div>

    <section class="content-section">
        <div class="filters-grid">
            <div class="filter-item">
                <label for="consentSearchInput">Search</label>
                <input type="text" id="consentSearchInput" placeholder="Search parent name, email, or phone">
            </div>
            <div class="filter-item">
                <label for="consentStartDate">From</label>
                <input type="date" id="consentStartDate">
            </div>
            <div class="filter-item">
                <label for="consentEndDate">To</label>
                <input type="date" id="consentEndDate">
            </div>
            <div class="filter-actions">
                <button type="button" id="clearConsentFiltersBtn" class="btn btn-secondary">Clear Filters</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Parent / Guardian</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Agreed</th>
                        <th>Agreed Date</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="consentsTableBody">
                    <tr>
                        <td colspan="6" class="loading">Loading privacy consents...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<style>
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .filter-item label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }

    .filter-item input,
    .filter-item select {
        width: 100%;
        padding: 10px;
        border: 1px solid #dcdcdc;
        border-radius: 6px;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .table-container {
        background: #fff;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th,
    table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        text-align: left;
    }

    table th {
        background: #f7f9fc;
        font-weight: 600;
        color: #555;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-pill.agreed {
        background: rgba(46, 204, 113, 0.15);
        color: #1e8449;
    }

    .status-pill.declined {
        background: rgba(231, 76, 60, 0.15);
        color: #c0392b;
    }

    .loading,
    .empty-state {
        text-align: center;
        padding: 30px 0;
        color: #777;
    }
</style>

<?php include 'includes/footer.php'; ?>

