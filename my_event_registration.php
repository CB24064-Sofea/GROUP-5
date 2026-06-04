<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Bar</title>
    <link rel="stylesheet" href="../STYLE/CSS/Module1/topBar_CSS.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<div class="topbar">
    <div class="topbar-left">
        <img src="../Images/logo.png" alt="Logo" class="topbar-logo">

        <h5>FK STUDENT CLUB & EVENT MANAGEMENT SYSTEM</h5></h5>
    </div>

    <div class="user-profile-dropdown dropdown">
        <div class="user-info-text d-none d-lg-block">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <span class="user-id"><?php echo htmlspecialchars($_SESSION['studentID']); ?></span>
        </div>
        
        <?php if (!empty($_SESSION['userProfilePicture'])): ?> 
            <img src="../Module 1/uploads/<?php echo $_SESSION['userProfilePicture']; ?>"  
                class="profile-pic dropdown-toggle"
                id="userDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                alt="">
        <?php else: ?>
            <div class="profile-pic empty-profile dropdown-toggle"
                id="userDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"></div>
        <?php endif; ?>
        
        <div class="dropdown">
            <ul class="dropdown-menu shadow border-0" aria-labelledby="userDropdown">
                <li><a class="dropdown-item py-2" href="../Module 1/profile.php"><i class="bi bi-person me-2 text-gray-400"></i> My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="../Module 1/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>