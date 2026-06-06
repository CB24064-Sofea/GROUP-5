<?php
require_once 'auth.php';
requireLogin();
if (!isCommittee()) { header("Location: index.php"); exit(); }
$clubID = getCommitteeClubID(getUserID());
include 'header.php';
include 'sidebar.php';

$events = $conn->query("SELECT eventID, eventName FROM event WHERE clubID = $clubID");
?>
<div class="workspace-stack">
    <div class="page-title">Manage Participants</div>
    <div class="form-card-container">
        <?php while ($ev = $events->fetch_assoc()): ?>
            <h3><?php echo htmlspecialchars($ev['eventName']); ?></h3>
            <?php
            $parts = $conn->query("SELECT u.name, er.registrationStatus FROM event_registration er JOIN user u ON er.userID=u.userID WHERE er.eventID={$ev['eventID']}");
            ?>
            <table><thead><tr><th>Name</th><th>Status</th></tr></thead><tbody>
            <?php while ($p = $parts->fetch_assoc()): ?>
                <tr><td><?php echo htmlspecialchars($p['name']); ?></td><td><?php echo $p['registrationStatus']; ?></td></tr>
            <?php endwhile; ?>
            </tbody></table>

            <h4>Waiting List</h4>
            <?php
            $wait = $conn->query("SELECT u.name, wl.position FROM waitinglist wl JOIN user u ON wl.userID=u.userID WHERE wl.eventID={$ev['eventID']} ORDER BY position");
            if ($wait->num_rows > 0): ?>
                <table><thead><tr><th>#</th><th>Name</th></tr></thead><tbody>
                <?php while ($w = $wait->fetch_assoc()): ?>
                    <tr><td><?php echo $w['position']; ?></td><td><?php echo htmlspecialchars($w['name']); ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            <?php else: echo "<p>No waiting list.</p>"; endif; ?>
            <hr>
        <?php endwhile; ?>
    </div>
</div>
<?php include 'footer.php'; ?>