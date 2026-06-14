<?php
function calculateRecognition($points) {
    if ($points >= 80) return "Outstanding participant; leadership priority";
    if ($points >= 50) return "Eligible for active student award";
    if ($points >= 20) return "Eligible for participation certificate";
    return "Warning / Reminder to participate more";
}

function updateStudentPoints($conn, $userID) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(a.points), 0) AS total FROM attendance a JOIN event_registration er ON a.registrationID = er.registrationID WHERE er.userID = ?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    $recog = calculateRecognition($total);
    $stmt = $conn->prepare("UPDATE student SET totalPoints = ?, recognitionLevel = ? WHERE userID = ?");
    $stmt->bind_param("iss", $total, $recog, $userID);
    $stmt->execute();
    $stmt->close();
}
?>