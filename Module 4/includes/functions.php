<?php
function calculateRecognition($points) {
    if ($points >= 80) return "Outstanding participant; leadership priority";
    if ($points >= 50) return "Eligible for active student award";
    if ($points >= 20) return "Eligible for participation certificate";
    return "Warning / Reminder to participate more";
}

function updateStudentPoints($conn, $userID) {
    $total = $conn->query("SELECT SUM(points) FROM attendance a 
                           JOIN event_registration er ON a.registrationID=er.registrationID 
                           WHERE er.userID='$userID'")->fetch_row()[0];
    $recog = calculateRecognition($total);
    $conn->query("UPDATE student SET totalPoints=$total, recognitionLevel='$recog' WHERE userID='$userID'");
}
?>