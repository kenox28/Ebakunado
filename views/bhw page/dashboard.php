<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BHW Dashboard</title>
    <link rel="stylesheet" href="/css/fonts.css" />
    <link rel="stylesheet" href="/css/base.css" />
    <link rel="stylesheet" href="/css/variables.css" />
    <link rel="stylesheet" href="/css/header.css" />
    <link rel="stylesheet" href="/css/sidebar.css" />
    <link rel="stylesheet" href="/css/dashboard.css" />
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <main class="dashboard-main">
        <section class="dashboard-section">
            <div class="dashboard-overview">
                <h2 class="dashboard-heading">Dashboard Overview</h2>
                <div class="card-wrapper">
                    <div class="card card-1">
                        <div class="card-info">
                            <span class="material-symbols-rounded">inbox</span>
                            <p class="card-title">Pending Child Requests</p>
                            <span class="card-info-btn material-symbols-rounded"  title="Shows the number of child vaccination requests waiting for approval.">info</span>
                        </div>
                        <div class="card-count">
                            <p class="card-number">101</p>
                        </div>
                    </div>
                    <div class="card card-2">
                        <div class="card-info">
                            <span class="material-symbols-rounded">vaccines</span>
                            <p class="card-title">Immunized Today</p>
                            <span class="card-info-btn material-symbols-rounded" title="Shows the total number of children vaccinated today.">info</span>
                        </div>
                        <div class="card-count">
                            <p class="card-number">23</p>
                        </div>
                    </div>
                    <div class="card card-3">
                        <div class="card-info">
                            <span class="material-symbols-rounded">event_available</span>
                            <p class="card-title">Upcoming Immunizations</p>
                            <span class="card-info-btn material-symbols-rounded" title="Shows the total number of scheduled immunizations for upcoming dates.">info</span>
                        </div>
                        <div class="card-count">
                            <p class="card-number">34</p>
                        </div>
                    </div>
                    <div class="card card-4">
                        <div class="card-info">
                            <span class="material-symbols-rounded">warning</span>
                            <p class="card-title">Missed Immunizations</p>
                            <span class="card-info-btn material-symbols-rounded" title="Shows the number of children who missed their scheduled immunizations.">info</span>
                        </div>
                        <div class="card-count">
                            <p class="card-number">51</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dashboard-charts">
                <h2 class="dashboard-heading">Chart Analysis</h2>
            </div>
        </section>
    </main>

    <script src="../../js/header-handler/profile-menu.js" defer></script>
    <script src="../../js/sidebar-handler/sidebar-menu.js" defer></script>
</body>

</html>