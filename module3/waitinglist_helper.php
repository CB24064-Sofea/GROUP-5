<?php

/**
 * Promote the first user in the waiting list
 * into event_registration when a slot becomes available.
 */
function promoteNextFromWaitingList(mysqli $conn, int $eventID): bool
{
    // Get first person in queue
    $stmt = $conn->prepare("
        SELECT waitingID, userID
        FROM waitinglist
        WHERE eventID = ?
        ORDER BY position ASC, registerAt ASC
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $eventID);
    $stmt->execute();

    $waiter = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$waiter) {
        return false;
    }

    // Register user into event
    $insert = $conn->prepare("
        INSERT INTO event_registration
        (
            eventID,
            userID,
            registrationDate,
            registrationStatus,
            cancellationDate
        )
        VALUES
        (
            ?,
            ?,
            NOW(),
            'Success',
            NULL
        )
    ");

    if (!$insert) {
        return false;
    }

    $insert->bind_param(
        "is",
        $eventID,
        $waiter['userID']
    );

    $success = $insert->execute();

    $insert->close();

    if (!$success) {
        return false;
    }

    // Remove promoted user from waiting list
    $delete = $conn->prepare("
        DELETE FROM waitinglist
        WHERE waitingID = ?
    ");

    if ($delete) {
        $delete->bind_param(
            "i",
            $waiter['waitingID']
        );

        $delete->execute();
        $delete->close();
    }

    // Recalculate queue positions
    $result = $conn->query("
        SELECT waitingID
        FROM waitinglist
        WHERE eventID = {$eventID}
        ORDER BY registerAt ASC
    ");

    if ($result) {

        $position = 1;

        while ($row = $result->fetch_assoc()) {

            $update = $conn->prepare("
                UPDATE waitinglist
                SET position = ?
                WHERE waitingID = ?
            ");

            if ($update) {

                $update->bind_param(
                    "ii",
                    $position,
                    $row['waitingID']
                );

                $update->execute();
                $update->close();
            }

            $position++;
        }
    }

    return true;
}