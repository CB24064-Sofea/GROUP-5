<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

$host = 'localhost'; $port = 3307; $dbname = 'fk_sc_ems'; $user = 'root'; $pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) { die("Database Connection Error: " . $e->getMessage()); }

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $posterData = !empty($_FILES['poster']['tmp_name']) ? file_get_contents($_FILES['poster']['tmp_name']) : null;
        $posterType = !empty($_FILES['poster']['type']) ? $_FILES['poster']['type'] : null;

        // Ensure these columns match your actual database schema
        $stmt = $pdo->prepare("INSERT INTO event (eventID, clubID, committeeID, eventName, eventDescription, eventDate, eventTime, venueLocation, maxParticipants, registrationDeadline, eventStatus, eventGeoLocationLat, eventGeoLocationLng, poster_data, poster_type) VALUES (?, 'CLUB01', 1, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 7 DAY, 'Open', ?, ?, ?, ?)");
        
        $stmt->execute([
            'EVT' . rand(1000, 9999), 
            $_POST['eventName'], 
            $_POST['eventDescription'], 
            $_POST['eventDate'], 
            $_POST['eventTime'], 
            $_POST['venueLocation'], 
            (int)$_POST['maxParticipants'], 
            $_POST['lat'], 
            $_POST['lng'], 
            $posterData, 
            $posterType
        ]);
        header("Location: event_management.php"); exit();
    } catch (Exception $e) { $error = "Error: " . $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Create Event - FK Management</title>
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
    <div class="flex items-center gap-8">
        <div class="text-right"><p class="text-[10px] text-gray-500 uppercase tracking-wider">Committee Portal</p><p class="text-sm font-bold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p></div>
    </div>
</header>

<div class="flex h-screen pt-[80px]">
    <nav class="w-[280px] fixed left-0 top-[80px] h-[calc(100vh-80px)] border-r border-gray-200 bg-white flex flex-col px-4 py-8">
        <div class="text-blue-600 font-bold mb-8 flex items-center gap-2 px-4"><span class="material-icons">groups</span> FK Management</div>
        <div class="space-y-2 flex-1">
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">dashboard</span> Dashboard</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">person</span> Profile</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="event_management.php"><span class="material-icons">build</span> Event Management</a>
            <a class="flex items-center gap-4 text-blue-700 bg-blue-50 p-3 rounded-lg font-medium" href="create_event.php"><span class="material-icons">add_circle</span> Create Event</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">how_to_reg</span> Create Attendance</a>
        </div>
        <a class="text-red-500 flex items-center gap-3 font-bold px-4 pt-4 border-t" href="#"><span class="material-icons">logout</span> Logout</a>
    </nav>

    <main class="ml-[280px] flex-1 p-8">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl border border-gray-100 shadow-sm">
            <h2 class="text-2xl font-extrabold mb-6">Create New Event</h2>
            <?php if ($error): ?><div class="p-4 mb-6 bg-red-100 text-red-800 rounded-lg text-sm"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input name="eventName" placeholder="Event Name *" class="w-full border p-3 rounded-lg bg-gray-50" required>
                <textarea name="eventDescription" placeholder="Event Description" class="w-full border p-3 rounded-lg bg-gray-50"></textarea>
                <div class="grid grid-cols-2 gap-4">
                    <input name="eventDate" type="date" class="w-full border p-3 rounded-lg bg-gray-50" required>
                    <input name="eventTime" type="time" class="w-full border p-3 rounded-lg bg-gray-50">
                </div>
                <input name="venueLocation" placeholder="Venue *" class="w-full border p-3 rounded-lg bg-gray-50" required>
                <input name="maxParticipants" type="number" placeholder="Max Participants" class="w-full border p-3 rounded-lg bg-gray-50">
                <div class="grid grid-cols-2 gap-4">
                    <input name="lat" placeholder="Latitude" class="w-full border p-3 rounded-lg bg-gray-50">
                    <input name="lng" placeholder="Longitude" class="w-full border p-3 rounded-lg bg-gray-50">
                </div>
                <input name="poster" type="file" class="w-full border p-3 rounded-lg bg-gray-50">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700">Submit Event</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>