<?php
session_start();
$host = "127.0.0.1:3307"; 
$user = "root";
$password = "";
$database = "group5";

// Auth Check: Allow only Students to apply/join clubs
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Student') {
    echo "
    <script>
        alert('Unauthorized access! Please login as a Student.');
        window.location='../login.php'; 
    </script>
    ";
    exit();
}

$link = mysqli_connect($host, $user, $password) or die("Connection failed: " . mysqli_connect_error());
mysqli_select_db($link, $database) or die("Database selection failed: " . mysqli_error($link));

$loggedInStudentID = $_SESSION['userID'];

// --- PROCESS JOIN CLUB SUBMISSION ---
if (isset($_POST['action']) && $_POST['action'] === 'join' && isset($_POST['clubID'])) {
    $targetClubID = intval($_POST['clubID']);
    
    // Check if user is already a member
    $checkQuery = "SELECT status FROM membership WHERE userID = '" . mysqli_real_escape_string($link, $loggedInStudentID) . "' AND clubID = $targetClubID LIMIT 1";
    $checkResult = mysqli_query($link, $checkQuery);
    
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $membershipRow = mysqli_fetch_assoc($checkResult);
        if ($membershipRow['status'] === 'Active') {
            $_SESSION['msg'] = "You are already an active member of this club!";
            $_SESSION['msgClass'] = "alert-error";
        } else {
            // Re-activate membership if it was inactive
            $updateQuery = "UPDATE membership SET status = 'Active', joinDate = NOW() WHERE userID = '" . mysqli_real_escape_string($link, $loggedInStudentID) . "' AND clubID = $targetClubID";
            if (mysqli_query($link, $updateQuery)) {
                $_SESSION['msg'] = "Successfully re-joined the club!";
                $_SESSION['msgClass'] = "alert-success";
            } else {
                $_SESSION['msg'] = "Error updating registration record: " . mysqli_error($link);
                $_SESSION['msgClass'] = "alert-error";
            }
        }
    } else {
        // Create a totally fresh membership record
        $insertQuery = "INSERT INTO membership (userID, clubID, joinDate, status) VALUES ('" . mysqli_real_escape_string($link, $loggedInStudentID) . "', $targetClubID, NOW(), 'Active')";
        if (mysqli_query($link, $insertQuery)) {
            $_SESSION['msg'] = "Success! You have officially joined the club.";
            $_SESSION['msgClass'] = "alert-success";
        } else {
            $_SESSION['msg'] = "Error processing your request: " . mysqli_error($link);
            $_SESSION['msgClass'] = "alert-error";
        }
    }
    
    header("Location: join_club.php");
    exit();
}

// Fetch Profile Image for Top Bar Navbar Header
$user_blob_string = "";
$image_mime_type = "image/jpeg"; 
$userQuery = "SELECT profilePhoto FROM user WHERE userID = '" . mysqli_real_escape_string($link, $loggedInStudentID) . "' LIMIT 1";
$userResult = mysqli_query($link, $userQuery);

if ($userResult && mysqli_num_rows($userResult) > 0) {
    $userRow = mysqli_fetch_assoc($userResult);
    if (!empty($userRow['profilePhoto'])) {
        $user_blob_string = base64_encode($userRow['profilePhoto']);
    }
}

// Fetch all Active status clubs that the user can join
$clubsQuery = "SELECT * FROM club WHERE status = 'Active' ORDER BY clubName ASC";
$clubsResult = mysqli_query($link, $clubsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Club - FK Student Club & Event Management</title>
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
            display: block;
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

        .profile-container {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
        }

        .profile-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .profile-fallback {
            font-size: 0.9rem;
            font-weight: 700;
            color: #3498db;
            text-transform: uppercase;
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
            background-color: #eaeaea; 
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
            text-decoration: none;
            color: #333;
        }

        .sub-nav-item:last-child {
            border-bottom: none;
        }

        .sub-nav-item:hover {
            background-color: #f1f2f6;
        }

        .active-sub {
            background-color: #3498db !important;
            color: white !important;
        }

        .btn-logout {
            margin-top: auto;
            background-color: #feeaee;
            color: #c0392b;
            border-color: #fab1a0;
            text-align: center;
            font-weight: 600;
            text-decoration: none;
        }
        
        .btn-logout:hover {
            background-color: #e74c3c;
            color: white;
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

        .alert {
            padding: 12px 18px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
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

        .club-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .club-card {
            background: #ffffff;
            border: 1px solid #b2bec3;
            border-radius: 6px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .club-card-header h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .club-advisor {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 12px;
        }

        .club-description {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .club-meta-info {
            border-top: 1px solid #eee;
            padding-top: 12px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #666;
        }

        .btn-join-submit {
            width: 100%;
            padding: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
            text-align: center;
        }

        .btn-join-submit:hover {
            background-color: #27ae60;
        }

        .no-records {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
            background: #fff;
            border: 1px solid #b2bec3;
            border-radius: 6px;
            font-style: italic;
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
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <div class="profile-container">
                <?php if (!empty($user_blob_string)): ?>
                    <img src="data:<?php echo $image_mime_type; ?>;base64,<?php echo $user_blob_string; ?>" alt="User Profile">
                <?php else: ?>
                    <span class="profile-fallback"><?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="app-container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <div class="submenu-container">
                    <a href="club_list.php" class="nav-item">Clubs</a>
                    <div class="submenu">
                        <a href="club_list.php" class="sub-nav-item">Registered Clubs</a>
                        <a href="join_club.php" class="sub-nav-item active-sub">Join Club</a>
                    </div>
                </div>
            </nav>
            <a href="../../module1/logout.php" class="btn-logout">Logout</a>
        </aside>

        <main class="main-content">
            <div class="workspace-stack">
                
                <h2 class="page-title">Available Clubs</h2>

                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="alert <?php echo $_SESSION['msgClass']; ?>">
                        <?php 
                            echo $_SESSION['msg']; 
                            unset($_SESSION['msg']);
                            unset($_SESSION['msgClass']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="club-grid">
                    <?php 
                    if ($clubsResult && mysqli_num_rows($clubsResult) > 0) {
                        while ($club = mysqli_fetch_assoc($clubsResult)) {
                            // Check if student is already a member of this loop iteration club
                            $mCheck = "SELECT status FROM membership WHERE userID = '" . mysqli_real_escape_string($link, $loggedInStudentID) . "' AND clubID = " . $club['clubID'] . " LIMIT 1";
                            $mCheckRes = mysqli_query($link, $mCheck);
                            $isMember = false;
                            if ($mCheckRes && mysqli_num_rows($mCheckRes) > 0) {
                                $mRow = mysqli_fetch_assoc($mCheckRes);
                                if ($mRow['status'] === 'Active') {
                                    $isMember = true;
                                }
                            }
                            ?>
                            <div class="club-card">
                                <div class="club-card-header">
                                    <h3><?php echo htmlspecialchars($club['clubName']); ?></h3>
                                    <div class="club-advisor">Advisor: <?php echo htmlspecialchars($club['advisor']); ?></div>
                                </div>
                                
                                <div class="club-description">
                                    <?php echo nl2br(htmlspecialchars($club['description'])); ?>
                                </div>

                                <div class="club-meta-info">
                                    <strong>Contact Email:</strong> <?php echo htmlspecialchars($club['email']); ?>
                                </div>

                                <form action="join_club.php" method="POST" onsubmit="return confirm('Are you sure you want to join <?php echo htmlspecialchars($club['clubName']); ?>?');">
                                    <input type="hidden" name="clubID" value="<?php echo $club['clubID']; ?>">
                                    <input type="hidden" name="action" value="join">
                                    
                                    <?php if ($isMember): ?>
                                        <button type="button" class="btn-join-submit" style="background-color: #7f8c8d; cursor: not-allowed;" disabled>Already Joined</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn-join-submit">Join Club</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='no-records' style='grid-column: 1/-1;'>No active clubs are currently available to join.</div>";
                    }
                    ?>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
<?php 
mysqli_close($link); 
?>