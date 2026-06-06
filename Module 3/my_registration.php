<?php
require_once 'auth.php';
requireLogin();
if (!isStudent()) { header("Location: index.php"); exit(); }
include 'header.php';
include 'sidebar.php';

$userID = getUserID();
$sql = "SELECT er.*, e.eventName, e.eventDate, e.eventTime FROM event_registration er JOIN event e ON er.eventID=e.eventID WHERE er.userID=? ORDER BY er.registrationDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="workspace-stack">
    <div class="page-title">My Event Registrations</div>
    <div class="form-card-container">
        <table>
            <thead><tr><th>Event</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['eventName']); ?></td>
                    <td><?php echo $row['eventDate']; ?></td>
                    <td><?php echo $row['eventTime']; ?></td>
                    <td><?php echo $row['registrationStatus']; ?></td>
                    <td>
                        <?php if ($row['registrationStatus'] == 'Success'): ?>
                            <a href="cancel_registration.php?regID=<?php echo $row['registrationID']; ?>" onclick="return confirm('Cancel registration?')">Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>