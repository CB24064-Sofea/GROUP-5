<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

// Only Committee and Admin can access
if (!isCommittee() && !isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$clubID = null;

/* =====================================================
   DETERMINE CLUB
===================================================== */
if (isCommittee()) {

    // Find committee's assigned club
    $stmt = $conn->prepare("
        SELECT clubID
        FROM club_committee
        WHERE userID = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $_SESSION['userID']);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    $clubID = $result['clubID'] ?? null;

    $stmt->close();

} else {

    // Admin can choose club
    $clubID = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;
}

/* =====================================================
   ADMIN CLUB DROPDOWN
===================================================== */
$allClubs = [];

if (isAdmin()) {

    $clubsQuery = $conn->query("
        SELECT clubID, clubName
        FROM club
        ORDER BY clubName ASC
    ");

    if ($clubsQuery) {
        $allClubs = $clubsQuery->fetch_all(MYSQLI_ASSOC);
    }

    if ($clubID <= 0 && !empty($allClubs)) {
        $clubID = (int)$allClubs[0]['clubID'];
    }
}

/* =====================================================
   FETCH CLUB DATA
===================================================== */
$clubName = "No Club Selected";
$totalMembers = 0;
$membersList = [];

if ($clubID) {

    // Club Name
    $stmt = $conn->prepare("
        SELECT clubName
        FROM club
        WHERE clubID = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $clubID);
    $stmt->execute();

    $clubData = $stmt->get_result()->fetch_assoc();

    $clubName = $clubData['clubName'] ?? 'Unknown Club';

    $stmt->close();

    // Total Members
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM membership
        WHERE clubID = ?
        AND status = 'Active'
    ");

    $stmt->bind_param("i", $clubID);
    $stmt->execute();

    $totalMembers = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    $stmt->close();

    // Member List
    $stmt = $conn->prepare("
        SELECT
            m.membershipID,
            u.userID,
            u.name,
            u.email,
            m.joinDate
        FROM membership m
        INNER JOIN user u
            ON m.userID = u.userID
        WHERE m.clubID = ?
        AND m.status = 'Active'
        ORDER BY u.name ASC
    ");

    $stmt->bind_param("i", $clubID);
    $stmt->execute();

    $membersList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
}

$urlContextSuffix = isAdmin()
    ? "?club_id=" . $clubID
    : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Membership List</title>
<link rel="stylesheet" href="/GROUP%205/standard.css">
</head>

<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="main-content">

        <h1 class="page-title">
            Membership List - <?= htmlspecialchars($clubName) ?>
        </h1>

        <?php if (isAdmin()): ?>

        <div class="form-card-container" style="margin-bottom:20px;">
            <form method="GET">

                <label><strong>Select Club:</strong></label>

                <select name="club_id"
                        onchange="this.form.submit()">

                    <?php foreach ($allClubs as $club): ?>

                    <option value="<?= $club['clubID'] ?>"
                        <?= ($clubID == $club['clubID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($club['clubName']) ?>
                    </option>

                    <?php endforeach; ?>

                </select>

            </form>
        </div>

        <?php endif; ?>

        <div class="stats">

            <div class="stat-card">

                <h3>Total Active Members</h3>

                <div class="number">
                    <?= $totalMembers ?>
                </div>

            </div>

        </div>

        <div class="form-card-container">

            <div style="display:flex;
                        justify-content:space-between;
                        align-items:center;
                        margin-bottom:15px;">

                <h3>Member Directory</h3>

                <?php if ($clubID): ?>

                <a href="manage_membership.php<?= $urlContextSuffix ?>"
                   class="btn btn-primary">
                    Manage Membership
                </a>

                <?php endif; ?>

            </div>

            <table class="data-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Join Date</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($membersList)): ?>

                    <tr>
                        <td colspan="5"
                            style="text-align:center;">
                            No active members found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php $no = 1; ?>

                    <?php foreach ($membersList as $member): ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td>
                            <?= htmlspecialchars($member['userID']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($member['name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($member['email']) ?>
                        </td>

                        <td>
                            <?= date(
                                'Y-m-d',
                                strtotime($member['joinDate'])
                            ) ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

</body>
</html>