        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <h2>EBAKUNADO</h2>
                    <span>Super Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="admin-management.php" class="nav-link <?php echo ($current_page == 'admin-management') ? 'active' : ''; ?>">
                            <span class="nav-icon">👨‍💼</span>
                            <span class="nav-text">Admin Management</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="user-management.php" class="nav-link <?php echo ($current_page == 'user-management') ? 'active' : ''; ?>">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">User Management</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="bhw-management.php" class="nav-link <?php echo ($current_page == 'bhw-management') ? 'active' : ''; ?>">
                            <span class="nav-icon">🏥</span>
                            <span class="nav-text">BHW Management</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="midwife-management.php" class="nav-link <?php echo ($current_page == 'midwife-management') ? 'active' : ''; ?>">
                            <span class="nav-icon">👩‍⚕️</span>
                            <span class="nav-text">Midwife Management</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="location-management.php" class="nav-link <?php echo ($current_page == 'location-management') ? 'active' : ''; ?>">
                            <span class="nav-icon">📍</span>
                            <span class="nav-text">Location Management</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="activity-logs.php" class="nav-link <?php echo ($current_page == 'activity-logs') ? 'active' : ''; ?>">
                            <span class="nav-icon">📋</span>
                            <span class="nav-text">Activity Logs</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="system-settings.php" class="nav-link <?php echo ($current_page == 'system-settings') ? 'active' : ''; ?>">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">System Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
