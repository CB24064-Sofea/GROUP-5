<?php

// 1. Check for the next person in line on the waiting list for this event
$stmt = $pdo->prepare("
    SELECT userID, waitingID 
    FROM waitinglist 
    WHERE eventID = ? 
    ORDER BY timestamp ASC 
    LIMIT 1
");
$stmt->execute([$event_id]);
$waiter = $stmt->fetch(PDO::FETCH_ASSOC);

if ($waiter) {
    $next_student_id = $waiter['userID'];
    $next_waiting_id = $waiter['waitingID'];

    // 2. Promote them: Insert into registration
    $promote = $pdo->prepare("
        INSERT INTO event_registration (eventID, userID, eventRegistrationDate, eventRegistrationStatus) 
        VALUES (?, ?, NOW(), 'Approved')
    ");
    $promote->execute([$event_id, $next_student_id]);

    // 3. Remove from waiting list
    $remove = $pdo->prepare("DELETE FROM waitinglist WHERE waitingID = ?");
    $remove->execute([$next_waiting_id]);
}
?>