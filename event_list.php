<?php
session_start();

// Ensure the user is logged in
$userID = $_SESSION['user_id'] ?? null;

$host = 'localhost';
$port = 3307;
$dbname = 'fk_sc_ems';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventID'])) {
    if (!$userID) {
        $err = "You must be logged in to register.";
    } else {
        // 1. Verify that the student exists in the database to prevent Error 1452
        $stmt = $pdo->prepare("SELECT studentID FROM student WHERE studentID = ?");
        $stmt->execute([$userID]);
        if (!$stmt->fetch()) {
            $err = "Error: Your User ID ($userID) is not registered in our student database.";
        } else {
            // 2. Proceed with registration logic
            $eventID = $_POST['eventID'];
            
            $cap = $pdo->prepare("SELECT maxParticipants, (SELECT COUNT(*) FROM event_registration WHERE eventID = ? AND registrationStatus = 'Registered') as reg FROM event WHERE eventID = ?");
            $cap->execute([$eventID, $eventID]);
            $c = $cap->fetch();
            
            if ($c && $c['reg'] >= $c['maxParticipants']) { 
                $err = "Event is full."; 
            } else {
                $check = $pdo->prepare("SELECT 1 FROM event_registration WHERE eventID = ? AND studentID = ?");
                $check->execute([$eventID, $userID]);
                if ($check->fetch()) { 
                    $err = "You are already registered for this event."; 
                } else {
                    $ins = $pdo->prepare("INSERT INTO event_registration (eventID, studentID, registrationDate, registrationStatus) VALUES (?,?,CURDATE(),'Registered')");
                    $ins->execute([$eventID, $userID]);
                    $msg = "Registered successfully!";
                }
            }
        }
    }
}

$search = $_GET['search'] ?? '';
$where = "WHERE eventStatus = 'Open' AND registrationDeadline > NOW() AND eventDate >= CURDATE()";
$params = [];

if ($search !== '') { 
    $where .= " AND eventName LIKE ?";
    $params[] = "%" . $search . "%";
}

$queryStr = "SELECT eventID, eventName, eventDate, eventTime, venueLocation, maxParticipants, 
            (SELECT COUNT(*) FROM event_registration WHERE eventID = e.eventID AND registrationStatus = 'Registered') as reg 
            FROM event e $where ORDER BY eventDate";

$stmt = $pdo->prepare($queryStr);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upcoming Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto bg-white rounded-xl shadow-md p-8">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Upcoming Events</h1>
        
        <?php if($msg): ?><div class='mb-4 p-3 bg-green-100 text-green-700 rounded'><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if($err): ?><div class='mb-4 p-3 bg-red-100 text-red-700 rounded'><?= htmlspecialchars($err) ?></div><?php endif; ?>
        
        <form method="GET" class="my-4">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <span class="material-symbols-outlined">search</span>
                </span>
                <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search upcoming events..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </form>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($events)): ?>
                <p class="text-gray-500 col-span-full py-4 text-center">No matching upcoming events found.</p>
            <?php else: ?>
                <?php foreach($events as $e): 
                    $left = max(0, $e['maxParticipants'] - $e['reg']); 
                ?>
                    <div class="border border-gray-200 rounded-xl p-5 shadow-sm bg-white flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-1"><?= htmlspecialchars($e['eventName']); ?></h3>
                            <p class="text-xs text-gray-400 mb-3">
                                <span class="material-symbols-outlined text-sm mr-0.5">calendar_month</span> 
                                <?= date('d M Y', strtotime($e['eventDate'])); ?> at <?= date('h:i A', strtotime($e['eventTime'])); ?>
                            </p>
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="material-symbols-outlined text-base text-gray-400 mr-1">location_on</span>
                                <?= htmlspecialchars($e['venueLocation']); ?>
                            </p>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center text-sm mb-3">
                                <span class="text-gray-500">Availability</span>
                                <span class="font-semibold <?= $left === 0 ? 'text-red-500' : 'text-gray-700' ?>">
                                    <?= $left; ?> / <?= $e['maxParticipants']; ?> seats left
                                </span>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="eventID" value="<?= htmlspecialchars($e['eventID']); ?>">
                                <?php if($left > 0): ?>
                                    <button type="submit" class="w-full bg-blue-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-blue-700 transition">Register Now</button>
                                <?php else: ?>
                                    <button disabled type="button" class="w-full bg-gray-200 text-gray-400 font-medium py-2 px-4 rounded-lg cursor-not-allowed">Full / Closed</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>