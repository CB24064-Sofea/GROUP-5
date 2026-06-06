<?php
require_once 'auth.php';
requireLogin();
include 'header.php';
include 'sidebar.php';

$userID = getUserID();
$role = getUserRole();
$committeeClubID = null;
if (isCommittee()) {
    $committeeClubID = getCommitteeClubID($userID);
}

// Delete event (only if owned)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $eventID = $_GET['delete'];
    if (isAdmin()) {
        $stmt = $conn->prepare("DELETE FROM event WHERE eventID = ?");
        $stmt->bind_param("i", $eventID);
        $stmt->execute();
        echo '<div class="alert alert-success">Event deleted.</div>';
    } elseif (isCommittee()) {
        $stmt = $conn->prepare("DELETE e FROM event e WHERE e.eventID = ? AND e.clubID = ?");
        $stmt->bind_param("ii", $eventID, $committeeClubID);
        $stmt->execute();
        if ($stmt->affected_rows > 0) echo '<div class="alert alert-success">Event deleted.</div>';
        else echo '<div class="alert alert-error">Unauthorized.</div>';
    }
}

// Fetch events
$sql = "SELECT e.*, c.clubName FROM event e JOIN club c ON e.clubID = c.clubID WHERE 1";
if (isCommittee()) {
    $sql .= " AND e.clubID = $committeeClubID";
}
$sql .= " ORDER BY e.eventDate DESC";
$result = $conn->query($sql);
?>
<div class="workspace-stack">
    <div class="page-title">📅 Events</div>
    <div class="form-card-container">
        <table>
            <thead>
                <tr><th>Event Name</th><th>Club</th><th>Date</th><th>Time</th><th>Venue</th><th>Capacity</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['eventName']); ?></td>
                    <td><?php echo htmlspecialchars($row['clubName']); ?></td>
                    <td><?php echo $row['eventDate']; ?></td>
                    <td><?php echo $row['eventTime']; ?></td>
                    <td><?php echo htmlspecialchars($row['venueLocation']); ?></td>
                    <td><?php echo $row['maxParticipants']; ?></td>
                    <td><?php echo $row['eventStatus']; ?></td>
                    <td>
                        <?php if (isAdmin() || (isCommittee() && $committeeClubID == $row['clubID'])): ?>
                            <a href="edit_event.php?id=<?php echo $row['eventID']; ?>">✏️ Edit</a> |
                            <a href="events.php?delete=<?php echo $row['eventID']; ?>" onclick="return confirm('Delete event?')">🗑️ Delete</a>
                        <?php endif; ?>
                        <?php if (isStudent()): ?>
                            <a href="register_event.php?id=<?php echo $row['eventID']; ?>">📝 Register</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>