<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config.php';

requireLogin();

$userID = $_SESSION['userID'];

if (isset($_POST['update'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    mysqli_query(
        $conn,
        "UPDATE user
         SET
            name = '$name',
            email = '$email',
            phoneNumber = '$phone'
         WHERE userID = '$userID'"
    );

    header("Location: /GROUP%205/module1/student/profile.php");
    exit();
}

$query = mysqli_query(
    $conn,
    "SELECT *
     FROM user
     WHERE userID = '$userID'
     LIMIT 1"
);

$user = mysqli_fetch_assoc($query);

$clubs = mysqli_query(
    $conn,
    "SELECT c.clubName
     FROM membership m
     JOIN club c ON m.clubID = c.clubID
     WHERE m.userID = '$userID'
     AND m.status = 'Active'"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>

    <link rel="stylesheet" href="/GROUP%205/standard.css">

    <style>
        .profile-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-card {
            background: #ffffff;
            border: 1px solid #d6dde5;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .profile-card h3 {
            color: #243b53;
            margin-bottom: 20px;
        }

        .club-item {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .club-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="main-content">

        <div class="workspace-stack">

            <h1 class="page-title">My Profile</h1>

            <div class="profile-wrapper">

                <div class="profile-card">

                    <form method="POST">

                        <div class="form-group-row">
                            <label>Name</label>
                            <input
                                type="text"
                                name="name"
                                class="input-control-select"
                                value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group-row">
                            <label>Email</label>
                            <input
                                type="email"
                                name="email"
                                class="input-control-select"
                                value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group-row">
                            <label>Phone Number</label>
                            <input
                                type="text"
                                name="phone"
                                class="input-control-select"
                                value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>"
                            >
                        </div>

                        <div class="form-actions-footer-bar">
                            <button type="submit" name="update" class="btn btn-submit">
                                Update Profile
                            </button>
                        </div>

                    </form>

                </div>

                <div class="profile-card">

                    <h3>My Club Membership Status</h3>

                    <?php if ($clubs && mysqli_num_rows($clubs) > 0): ?>

                        <?php while ($club = mysqli_fetch_assoc($clubs)): ?>

                            <div class="club-item">
                                <?= htmlspecialchars($club['clubName']) ?>
                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <p>You are not currently registered as an active member in any club.</p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>