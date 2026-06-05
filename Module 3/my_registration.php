<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '101'; 
    $_SESSION['username'] = 'Ahmad Zaki';
}

$host = 'localhost'; $port = 3307; $dbname = 'fk_sc_ems'; $user = 'root'; $pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) { die("Database Connection Error: " . $e->getMessage()); }

$error = ""; $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    try {
        $stmt = $pdo->prepare("DELETE FROM event_registration WHERE registrationID = ? AND studentID = ?");
        $stmt->execute([$_POST['registration_id'], $_SESSION['user_id']]);
        $success = "Your event registration has been successfully canceled.";
    } catch (Exception $e) { $error = "Transaction error: " . $e->getMessage(); }
}

try {
    $stmt = $pdo->prepare("
        SELECT r.registrationID, r.registrationStatus, e.eventName, e.eventDate, e.eventTime, e.venueLocation, e.maxParticipants
        FROM event_registration r
        JOIN event e ON r.eventID = e.eventID
        WHERE r.studentID = ?
        ORDER BY e.eventDate ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $registeredEvents = $stmt->fetchAll();
} catch (Exception $e) { $error = "Failed to synchronize: " . $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>My Registration - FK Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <style>body { font-family: 'Manrope', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="bg-[#f8fafc] text-slate-900">

<header class="fixed top-0 left-0 right-0 h-[80px] bg-white border-b border-gray-200 flex justify-between items-center px-8 z-50">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-600 flex items-center justify-center text-white font-bold rounded">FK</div>
        <h1 class="text-xl font-bold text-slate-800">FK Student Club & Event Management</h1>
    </div>
    <div class="text-right"><p class="text-[10px] text-gray-500 uppercase tracking-wider">Student Portal</p><p class="text-sm font-bold"><?= htmlspecialchars($_SESSION['username']) ?></p></div>
</header>

<div class="flex h-screen pt-[80px]">
    <nav class="w-[280px] fixed left-0 top-[80px] h-[calc(100vh-80px)] border-r border-gray-200 bg-white flex flex-col px-4 py-8">
        <div class="text-blue-600 font-bold mb-8 flex items-center gap-2 px-4"><span class="material-icons">groups</span> FK Management</div>
        <div class="space-y-2 flex-1">
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">dashboard</span> Dashboard</a>
            <a class="flex items-center gap-4 text-blue-700 bg-blue-50 p-3 rounded-lg font-medium" href="#"><span class="material-icons">event</span> My Registration</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="create_event.php"><span class="material-icons">add_circle</span> Create Event</a>
        </div>
    </nav>

    <main class="ml-[280px] flex-1 p-8">
        <h2 class="text-2xl font-bold text-slate-800 mb-8">My Registration</h2>
        <?php if ($error): ?><div class="p-4 mb-6 bg-red-100 text-red-800 rounded-lg text-sm"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="p-4 mb-6 bg-green-100 text-green-800 rounded-lg text-sm"><?= $success ?></div><?php endif; ?>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-gray-500 uppercase text-[11px] tracking-widest font-bold">
                        <th class="p-5">Event Name</th>
                        <th class="p-5">Date & Time</th>
                        <th class="p-5">Location</th>
                        <th class="p-5">Status</th>
                        <th class="p-5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registeredEvents as $e): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-5 font-bold"><?= htmlspecialchars($e['eventName']) ?></td>
                        <td class="p-5"><?= htmlspecialchars($e['eventDate'] . ' ' . $e['eventTime']) ?></td>
                        <td class="p-5"><?= htmlspecialchars($e['venueLocation']) ?></td>
                        <td class="p-5"><span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold"><?= htmlspecialchars($e['registrationStatus']) ?></span></td>
                        <td class="p-5 text-center">
                            <form method="POST" onsubmit="return confirm('Cancel this registration?');">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="registration_id" value="<?= $e['registrationID'] ?>">
                                <button class="text-red-500 font-bold text-xs hover:underline">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>