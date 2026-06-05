<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "127.0.0.1:3307";
$user = "root";
$password = "";
$database = "fk_club_system";

$link = mysqli_connect($host, $user, $password);

if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

$db_selected = mysqli_select_db($link, $database);
if (!$db_selected) {
    die("Database selection failed: " . mysqli_error($link));
}

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and clean inputs from form fields matching the exact schema columns
    $clubID = mysqli_real_escape_string($link, $_POST['clubID']);
    $studentID = mysqli_real_escape_string($link, $_POST['studentID']);
    $position = mysqli_real_escape_string($link, $_POST['position']);
    
    // For schema completeness, we default eventID to 'NONE' if not used for basic club committees
    $eventID = "NONE"; 

    if (isset($_POST['assign_committee'])) {
        // Step A: Check if this student is already assigned a role in this club
        $checkQuery = "SELECT * FROM committee WHERE studentID = '$studentID' AND clubID = '$clubID'";
        $checkResult = mysqli_query($link, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "Error: This student is already assigned a committee role in this club.";
            $messageClass = "alert-error";
        } else {
            // Step B: Insert new assignment record matching your database schema structure
            $insertQuery = "INSERT INTO committee (studentID, clubID, eventID, position) 
                            VALUES ('$studentID', '$clubID', '$eventID', '$position')";
            
            if (mysqli_query($link, $insertQuery)) {
                $message = "Success: Committee member assigned successfully!";
                $messageClass = "alert-success";
            } else {
                $message = "Database Error: Unable to assign member. " . mysqli_error($link);
                $messageClass = "alert-error";
            }
        }
    } elseif (isset($_POST['update_committee'])) {
        // Step C: Update the position for the student in that club
        $updateQuery = "UPDATE committee 
                        SET position = '$position' 
                        WHERE studentID = '$studentID' AND clubID = '$clubID'";
        
        if (mysqli_query($link, $updateQuery)) {
            if (mysqli_affected_rows($link) > 0) {
                $message = "Success: Committee details updated successfully!";
                $messageClass = "alert-success";
            } else {
                $message = "Notice: No changes were made, or no matching record was found to update.";
                $messageClass = "alert-error";
            }
        } else {
            $message = "Database Error: Unable to update details. " . mysqli_error($link);
            $messageClass = "alert-error";
        }
    }
}

// 3. Fetch Dynamic Dropdown List Choices
// Get list of active clubs
$clubsResult = mysqli_query($link, "SELECT clubID, clubName FROM club ORDER BY clubName ASC");

// Get list of students from user/student records
$studentsResult = mysqli_query($link, "SELECT studentID, studentName FROM student ORDER BY studentID ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Committee- FK Student Club & Event Management</title>
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
            color: white; /* Fixed typo from colour to color */
            border-color: #b2bec3;
        }

        .submenu-container {
            border: 1px solid #b2bec3;
            border-radius: 5px;
            background-color: #fafafa;
            overflow: hidden;
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
            max-width: 800px;
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

        .form-card {
            background-color: #ffffff;
            border: 1px solid #b2bec3;
            border-radius: 6px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #b2bec3;
            border-radius: 5px;
            font-size: 1rem;
            background-color: #ffffff;
            color: #333;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #3498db;
        }

        .actions-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
        }

        .btn-action {
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            border: 1px solid #b2bec3;
            transition: all 0.2s;
            min-width: 180px;
            text-align: center;
        }

        .btn-assign {
            background-color: #ffffff;
            color: #2c3e50;
        }

        .btn-assign:hover {
            background-color: #f1f2f6;
            border-color: #7f8c8d;
        }

        .btn-update {
            background-color: #ffffff;
            color: #2c3e50;
        }

        .btn-update:hover {
            background-color: #f1f2f6;
            border-color: #7f8c8d;
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
            font-size: 0.95rem;
            border: 1px solid transparent;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
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
                <a href="admin_dashboard.php" class="nav-item">Dashboard</a>
                <a href="manage_committee.php" class="nav-item active-main-menu">Committee</a>
                
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
                
                <h2 class="page-title">Manage Club Committee</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $messageClass; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="manage_committee.php" method="POST" class="form-card">
                    
                    <div class="form-group">
                        <label for="clubID">Club Name</label>
                        <select name="clubID" id="clubID" class="form-control" required>
                            <option value="">-- Select Club --</option>
                            <?php 
                            if ($clubsResult && mysqli_num_rows($clubsResult) > 0) {
                                while ($clubRow = mysqli_fetch_assoc($clubsResult)) {
                                    echo "<option value='".htmlspecialchars($clubRow['clubID'])."'>".htmlspecialchars($clubRow['clubName'])."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="studentID">Select Student</label>
                        <select name="studentID" id="studentID" class="form-control" required>
                            <option value="">-- Select Student --</option>
                            <?php 
                            if ($studentsResult && mysqli_num_rows($studentsResult) > 0) {
                                while ($studentRow = mysqli_fetch_assoc($studentsResult)) {
                                    // Pulled studentName so it's readable for users
                                    echo "<option value='".htmlspecialchars($studentRow['studentID'])."'>".htmlspecialchars($studentRow['studentID'])." - ".htmlspecialchars($studentRow['studentName'])."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <select name="position" id="position" class="form-control" required>
                            <option value="">-- Select Position Role --</option>
                            <option value="President">President</option>
                            <option value="Vice President">Vice President</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Treasurer">Treasurer</option>
                            <option value="Committee Member">Committee Member</option>
                        </select>
                    </div>

                    <div class="actions-row">
                        <button type="submit" name="assign_committee" class="btn-action btn-assign">Assign Committee</button>
                        <button type="submit" name="update_committee" class="btn-action btn-update">Update</button>
                    </div>

                </form>

            </div>
        </main>
    </div>

</body>
</html>
<?php 
mysqli_close($link); 
?>