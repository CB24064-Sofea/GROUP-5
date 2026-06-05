<?php
// 1. Database Connection Configuration
$host = "127.0.0.1:3307";
$user = "root";
$password = "";
$database = "fk_club_system";

// Connect to the database server
$link = mysqli_connect($host, $user, $password) or die("Connection failed: " . mysqli_connect_error());

// Select the database
mysqli_select_db($link, $database) or die("Database selection failed: " . mysqli_error($link));

// Initialize messages for user feedback
$message = "";
$messageClass = "";

// Initialize variables to hold existing data
$clubID = "";
$clubName = "";
$advisor = "";
$email = "";
$description = "";
$status = "Active"; // Default fallback

// 2. Fetch Existing Data to Pre-fill the Form
if (isset($_GET['id'])) {
    $editID = mysqli_real_escape_string($link, $_GET['id']);
    
    $fetchQuery = "SELECT * FROM club WHERE clubID = '$editID'";
    $fetchResult = mysqli_query($link, $fetchQuery);
    
    if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
        $row = mysqli_fetch_assoc($fetchResult);
        $clubID = $row['clubID'];
        $clubName = $row['clubName'];
        $advisor = $row['advisor'];
        $email = $row['email'];
        $description = $row['description'];
        $status = $row['status'];
    } else {
        $message = "Club not found!";
        $messageClass = "alert-error";
    }
} else {
    $message = "No club ID provided for editing.";
    $messageClass = "alert-error";
}

// 3. Process Form Submission to UPDATE the record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clubId'])) {
    
    // Get values from the form inputs
    $clubID = $_POST['clubId']; // Keep the ID from hidden or read-only input
    $clubName = $_POST['clubName'];
    $advisor = $_POST['advisorName'];
    $email = $_POST['email'];
    $description = $_POST['description'];
    $status = $_POST['clubStatus'];

    // Clean inputs to avoid syntax issues with quotes
    $clubID = mysqli_real_escape_string($link, $clubID);
    $clubName = mysqli_real_escape_string($link, $clubName);
    $advisor = mysqli_real_escape_string($link, $advisor);
    $email = mysqli_real_escape_string($link, $email);
    $description = mysqli_real_escape_string($link, $description);
    $status = mysqli_real_escape_string($link, $status);

    // SQL Update Query targeting the specific record
    $query = "UPDATE club SET 
              clubName = '$clubName', 
              description = '$description', 
              email = '$email', 
              status = '$status', 
              advisor = '$advisor' 
              WHERE clubID = '$clubID'";

    // Execute the update query
    $result = mysqli_query($link, $query);

    if ($result) {
        $message = "Club updated successfully!"; // Changes prompt message format to show Updated status
        $messageClass = "alert-success";
    } else {
        $message = "Update query failed: " . mysqli_error($link);
        $messageClass = "alert-error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Club - FK Student Club & Event Management</title>
    <style>
        /* Base Resets & Typography */
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

        /* Header Styling */
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

        .logo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
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

        /* Layout Container */
        .app-container {
            display: flex;
            flex: 1;
        }

        /* Sidebar Styling */
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
            color: #333333;
        }

        .nav-item:hover, .btn-logout:hover {
            background-color: #f1f2f6;
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
        }

        .sub-nav-item:last-child {
            border-bottom: none;
        }

        .active-sub {
            background-color: #3498db !important;
            color: white;
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
            background-color: #e74c3c;
            color: white;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            overflow-y: auto;
        }

        /* Form Container */
        .form-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 700px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }

        .form-title {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.6rem;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        /* Feedback Status Banners */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }

        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus, 
        .form-group textarea:focus, 
        .form-group select:focus {
            border-color: #3498db;
        }

        /* Input styling specifically for Read-Only Primary Key */
        .form-group input[readonly] {
            background-color: #f1f2f6;
            color: #7f8c8d;
            cursor: not-allowed;
            border-color: #dcdde1;
        }

        /* Form Actions (Buttons) */
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 24px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }

        .btn-profile {
            background-color: #7f8c8d;
            color: white;
        }

        .btn-profile:hover {
            background-color: #95a5a6;
        }

        .btn-save {
            background-color: #2ecc71;
            color: white;
            min-width: 120px;
        }

        .btn-save:hover {
            background-color: #27ae60;
        }

        .btn-cancel {
            background-color: #95a5a6;
            color: white;
            min-width: 120px;
        }

        .btn-cancel:hover {
            background-color: #7f8c8d;
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
            <button class="btn btn-profile">Profile</button>
        </div>
    </header>

    <div class="app-container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="../admin_dashboard.php" class="nav-item">Dashboard</a>
                <a href="manage_committee.php" class="nav-item">Committee</a>

                <div class="submenu-container">
                    <button class="nav-item">Clubs</button>
                    <div class="submenu">
                        <button class="sub-nav-item">Manage Club</button>
                        <button class="sub-nav-item active-sub">Edit Club</button>
                    </div>
                </div>

                <button class="nav-item">Events</button>
                <button class="nav-item">Attendance</button>
                <button class="nav-item">Reports</button>
            </nav>
            <button class="btn-logout">Logout</button>
        </aside>

        <main class="main-content">
            <div class="form-container">
                <h2 class="form-title">Edit Club Details</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $messageClass; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="clubId">Club ID</label>
                        <input type="text" id="clubId" name="clubId" value="<?php echo htmlspecialchars($clubID); ?>" readonly required>
                    </div>

                    <div class="form-group">
                        <label for="clubName">Club Name</label>
                        <input type="text" id="clubName" name="clubName" maxlength="100" placeholder="Enter Club Name" value="<?php echo htmlspecialchars($clubName); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="advisorName">Advisor Name</label>
                        <input type="text" id="advisorName" name="advisorName" maxlength="150" placeholder="Enter Advisor Name" value="<?php echo htmlspecialchars($advisor); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="100" placeholder="Enter Email Address" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Enter Club Description" required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="clubStatus">Club Status</label>
                        <select id="clubStatus" name="clubStatus" required>
                            <option value="Active" <?php echo ($status == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-save">Save</button>
                        <button type="button" class="btn btn-cancel" onclick="window.location.href='manage_club.php';">Cancel</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
<?php 
// 4. Close connection
mysqli_close($link); 
?>