<?php
require_once 'auth.php';
requireLogin();
include 'header.php';
include 'sidebar.php';

// Single table report: events
$single = $conn->query("SELECT eventID, eventName, eventDate, venueLocation, maxParticipants, eventStatus FROM event ORDER BY eventDate DESC");

// Join table report: events with participant count and club name
$join = $conn->query("SELECT e.eventName, c.clubName, COUNT(er.registrationID) as participants, e.maxParticipants FROM event e JOIN club c ON e.clubID=c.clubID LEFT JOIN event_registration er ON e.eventID=er.eventID AND er.registrationStatus='Success' GROUP BY e.eventID");
?>
<div class="workspace-stack">
    <div class="page-title">Event Reports</div>
    <div class="form-card-container">
        <h3>Single Table: All Events</h3>
        <table><thead><tr><th>ID</th><th>Name</th><th>Date</th><th>Venue</th><th>Capacity</th><th>Status</th></tr></thead><tbody>
        <?php while ($r = $single->fetch_assoc()): ?>
            <tr><td><?php echo $r['eventID']; ?></td><td><?php echo htmlspecialchars($r['eventName']); ?></td><td><?php echo $r['eventDate']; ?></td><td><?php echo htmlspecialchars($r['venueLocation']); ?></td><td><?php echo $r['maxParticipants']; ?></td><td><?php echo $r['eventStatus']; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>Join Table: Events with Participant Count & Club</h3>
        <table><thead><tr><th>Event</th><th>Club</th><th>Participants</th><th>Capacity</th></tr></thead><tbody>
        <?php while ($j = $join->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($j['eventName']); ?></td><td><?php echo htmlspecialchars($j['clubName']); ?></td><td><?php echo $j['participants']; ?></td><td><?php echo $j['maxParticipants']; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>
    </div>
</div>
<?php include 'footer.php'; ?>