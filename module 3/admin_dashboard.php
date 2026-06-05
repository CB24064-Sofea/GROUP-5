<?php
// Start session to safely check login properties or variables if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Database Connection Configuration (Matching manage_club.php exactly)
$host = "127.0.0.1:3307";
$user = "root";
$password = "";
$database = "fk_club_system";

// Connect to the database server
$link = mysqli_connect($host, $user, $password);

// If the connection fails, stop the code and show an error message
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

// Select the database inside MySQL
$db_selected = mysqli_select_db($link, $database);
if (!$db_selected) {
    die("Database selection failed: " . mysqli_error($link));
}

// --- Metric A: Count Total Clubs ---
$queryTotal = "SELECT COUNT(clubID) AS total_clubs FROM club";
$resultTotal = mysqli_query($link, $queryTotal);
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalClubsCount = $rowTotal['total_clubs'] ?? 0;

// --- Metric B: Count Active Clubs Only ---
$queryActive = "SELECT COUNT(clubID) AS active_clubs FROM club WHERE status = 'Active'";
$resultActive = mysqli_query($link, $queryActive);
$rowActive = mysqli_fetch_assoc($resultActive);
$activeClubsCount = $rowActive['active_clubs'] ?? 0;

// --- Metric C: Count Total Students ---
$queryStudents = "SELECT COUNT(studentID) AS total_students FROM student";
$resultStudents = mysqli_query($link, $queryStudents);

if ($resultStudents) {
    $rowStudents = mysqli_fetch_assoc($resultStudents);
    $totalStudentsCount = $rowStudents['total_students'] ?? 0;
} else {
    $totalStudentsCount = 0; 
}

// Calculate percentages for the pie chart rendering logic
if ($totalClubsCount > 0) {
    $activePercent = ($activeClubsCount / $totalClubsCount) * 100;
    $inactivePercent = 100 - $activePercent;
} else {
    $activePercent = 0;
    $inactivePercent = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Dashboard- FK Student Club & Event Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-header {
            background-color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            border-bottom: 2px solid #e0e0e0;
            height: 70px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-placeholder img {
            height: 45px;
            width: auto;
            object-fit: contain;
        }

        .header-left h1 {
            font-size: 1.4rem;
            color: #2c3e50;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-name {
            font-weight: 600;
            color: #555;
        }

        .app-container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 240px;
            background-color: #ffffff;
            border-right: 2px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 15px;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-item, .sub-nav-item, .btn-logout {
            width: 100%;
            padding: 12px 15px;
            background: none;
            border: 1px solid #dcdde1;
            border-radius: 5px;
            text-align: left;
            font-size: 0.95rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;

            text-decoration: none;
            display: block;
            color: #333;
        }

        .active-main-menu {
            background-color: #3498db;
            colour: white;
            border-color: #b2bec3;
        }

        .submenu-container {
            border: 1px solid #b2bec3;
            border-radius: 5px;
            background-color: #ffffff;
        }

        .submenu-container .nav-item {
            border: none;
            border-bottom: 1px solid #dcdde1;
            border-radius: 0;
            background-color: #f1f2f6;
            font-weight: bold;
        }

        .submenu {
            display: flex;
            flex-direction: column;
        }

        .sub-nav-item {
            border: none;
            border-bottom: 1px solid #eee;
            border-radius: 0;
            padding-left: 30px;
            font-size: 0.9rem;
            color: #333;
        }

        .sub-nav-item:last-child {
            border-bottom: none;
        }

        .btn-logout {
            margin-top: auto;
            background-color: #feeaee;
            color: #c0392b;
            border-color: #fab1a0;
            text-align: center;
            font-weight: 600;
        }

        .btn-logout:hover {
            background-color: #fafafa;
            border-color: #dcdde1;
            color: red;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .workspace-stack {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .page-title {
            text-align: center;
            font-size: 1.6rem;
            color: #2c3e50;
            border: 1px solid #b2bec3;
            background-color: #ffffff;
            padding: 12px;
            border-radius: 5px;
        }

        .stats-section {
            background-color: #ffffff;
            border: 1px solid #b2bec3;
            border-radius: 6px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fa;
            border: 1px solid #dcdde1;
            padding: 15px 25px;
            border-radius: 5px;
        }

        .stats-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .stats-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3498db;
            background-color: #ffffff;
            border: 1px solid #b2bec3;
            padding: 5px 25px;
            border-radius: 4px;
            min-width: 90px;
            text-align: center;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .chart-card {
            background-color: #ffffff;
            border: 1px solid #b2bec3;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
        }

        .chart-card h3 {
            font-size: 0.95rem;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 1px dashed #dcdde1;
            padding-bottom: 8px;
            font-weight: 600;
        }

        .bar-chart-container {
            height: 160px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 30px;
        }

        .bar-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .graphic-bar {
            width: 45px;
            background-color: #2c3e50;
            border-radius: 3px 3px 0 0;
        }

        .pie-chart-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: conic-gradient(
                #2c3e50 0% <?php echo $activePercent; ?>%, 
                #7f8c8d <?php echo $activePercent; ?>% 100%
            );
            margin: 10px auto;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            display: inline-block;
            border-radius: 2px;
            margin-right: 5px;
        }

        .btn-profile {
            background-color: #7f8c8d; 
            color: white; 
            padding: 6px 12px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-left">
            <div class="logo-placeholder">
                <img src="UMP LOGO.png" alt="Logo">
            </div>
            <h1>FK Student Club & Event Management</h1>
        </div>
        <div class="header-right">
            <span class="admin-name">Admin Name</span>
            <a href="#" class="btn-profile">profile</a>
        </div>
    </header>

    <div class="app-container">
        
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item active-main-menu">Dashboard</a>
                <a href="manage_committee.php" class="nav-item">Committee</a>
                
                <div class="submenu-container">
                    <div class="nav-item" style="background-color: #f1f2f6; font-weight: bold; cursor: default;">Clubs</div>
                    <div class="submenu">
                        <a href="manageClub/manage_club.php" class="sub-nav-item">Manage Club</a>
                        <a href="create_club.php" class="sub-nav-item">Create Club</a>
                    </div>
                </div>

                <a href="#" class="nav-item">Events</a>
                <a href="#" class="nav-item">Attendance</a>
                <a href="#" class="nav-item">Reports</a>
            </nav>
            
            <a href="#" class="btn-logout" onerror="this.href='logout.php'">Logout</a>
        </aside>

        <main class="main-content">
            <div class="workspace-stack">
                
                <h2 class="page-title">Club Dashboard</h2>

                <section class="stats-section">
                    
                    <div class="stats-row">
                        <span class="stats-label">Total Clubs</span>
                        <div class="stats-number"><?php echo (int)$totalClubsCount; ?></div>
                    </div>

                    <div class="stats-row">
                        <span class="stats-label">Active Clubs</span>
                        <div class="stats-number"><?php echo (int)$activeClubsCount; ?></div>
                    </div>

                    <div class="stats-row">
                        <span class="stats-label">Total Students Joined</span>
                        <div class="stats-number"><?php echo (int)$totalStudentsCount; ?></div>
                    </div>

                </section>

                <div class="charts-grid">
                    
                    <div class="chart-card">
                        <h3>Student Distribution by Club</h3>
                        <div class="bar-chart-container">
                            <div class="bar-group">
                                <span>45</span>
                                <div class="graphic-bar" style="height: 100px;"></div>
                                <span>Club A</span>
                            </div>
                            <div class="bar-group">
                                <span>22</span>
                                <div class="graphic-bar" style="height: 50px;"></div>
                                <span>Club B</span>
                            </div>
                            <div class="bar-group">
                                <span>35</span>
                                <div class="graphic-bar" style="height: 80px;"></div>
                                <span>Club C</span>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3>Active vs Inactive Clubs</h3>
                        <div class="pie-chart-circle"></div>
                        <div class="chart-legend">
                            <div>
                                <span class="legend-dot" style="background-color: #2c3e50;"></span>
                                Active (<?php echo round($activePercent); ?>%)
                            </div>
                            <div>
                                <span class="legend-dot" style="background-color: #7f8c8d;"></span>
                                Inactive (<?php echo round($inactivePercent); ?>%)
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>

</body>
</html>
<?php 
mysqli_close($link); 
?>