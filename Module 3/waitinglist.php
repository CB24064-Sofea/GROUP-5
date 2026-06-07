<?php
function promoteNextFromWaitingList(mysqli $conn, int $eventID): bool
{
    $stmt = $conn->prepare("
        SELECT waitingID, userID
        FROM waitinglist
        WHERE eventID = ?
        ORDER BY position ASC, registerAt ASC
        LIMIT 1
    ");
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $waiter = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$waiter) {
        return false;
    }

    $insert = $conn->prepare("
        INSERT INTO event_registration
            (eventID, userID, registrationDate, registrationStatus, cancellationDate)
        VALUES
            (?, ?, NOW(), 'Success', NOW())
    ");
    $insert->bind_param("is", $eventID, $waiter['userID']);
    $insert->execute();
    $insert->close();

    $delete = $conn->prepare("DELETE FROM waitinglist WHERE waitingID = ?");
    $delete->bind_param("i", $waiter['waitingID']);
    $delete->execute();
    $delete->close();

    return true;
}
?>