<?php
session_start();

// Restore session from JWT token if session expired
require_once __DIR__ . '/../../php/supabase/shared/restore_session_from_jwt.php';
restore_session_from_jwt();

if (!isset($_SESSION['super_admin_id'])) {
    header("Location: login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Consents</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/super-admin/header.css" />
    <link rel="stylesheet" href="css/super-admin/sidebar.css" />
    <link rel="stylesheet" href="css/super-admin/table-style.css?v=1.0.4" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Privacy Consents</h1>
            <p>Monitor Data Privacy Act acknowledgements captured during parent onboarding.</p>
        </div>

        <section class="content-section">
            <div class="data-table-card">
                <div class="data-table-toolbar data-table-toolbar--stack">
                    <div class="data-table-toolbar__titles">
                        <h2 class="data-table-title">Parent Consent Records</h2>
                        <p class="data-table-subtitle">Search and review consent logs with timestamp and IP details.</p>
                    </div>
                    <div class="data-table-actions privacy-consent-actions">
                        <div class="data-table-search" id="consentSearchWrap">
                            <span class="material-symbols-rounded data-table-search__icon">search</span>
                            <input type="text" id="consentSearchInput" class="data-table-search__input"
                                placeholder="Search parent name, email, or phone" />
                            <button type="button" id="consentSearchClear" class="data-table-search__clear" aria-label="Clear search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <label class="filter-control">
                            <span>From</span>
                            <input type="date" id="consentStartDate" />
                        </label>
                        <label class="filter-control">
                            <span>To</span>
                            <input type="date" id="consentEndDate" />
                        </label>
                        <button type="button" id="clearConsentFiltersBtn" class="btn btn-secondary btn-icon">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">Parent / Guardian</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Agreed</th>
                                <th scope="col">Agreed Date</th>
                                <th scope="col">IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="consentsTableBody">
                            <tr class="data-table__message-row loading">
                                <td colspan="6">Loading privacy consents...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pager">
                    <p class="page-info" id="consentPageInfo">Showing 0-0 of 0</p>
                    <div class="pager-controls">
                        <button type="button" class="pager-btn" id="consentPrevBtn" data-page="1" disabled>
                            <span class="material-symbols-rounded">chevron_left</span>
                            Prev
                        </button>
                        <button type="button" class="pager-btn" id="consentNextBtn" data-page="1" disabled>
                            Next
                            <span class="material-symbols-rounded">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="js/header-handler/profile-menu.js" defer></script>
    <script src="js/sidebar-handler/sidebar-menu.js" defer></script>
    <script src="js/utils/skeleton-loading.js" defer></script>
    <script src="js/supabase_js/superadmin/privacy-consents.js?v=1.0.5" defer></script>

    <style>
        .privacy-consent-actions {
            align-items: flex-end;
        }

        .privacy-consent-actions .filter-control {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 1.2rem;
            color: #1f2937;
        }

        .privacy-consent-actions .filter-control input {
            width: 180px;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font: inherit;
        }

        @media (max-width: 768px) {
            .privacy-consent-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .privacy-consent-actions .data-table-search,
            .privacy-consent-actions .filter-control input {
                width: 100%;
            }
        }
    </style>
</body>

</html>

