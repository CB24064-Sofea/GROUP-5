<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

$host = 'localhost'; $port = 3307; $dbname = 'fk_sc_ems'; $user = 'root'; $pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) { die("Database Connection Error: " . $e->getMessage()); }

$eventID = $_GET['id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM event WHERE eventID = ?");
$stmt->execute([$eventID]);
$event = $stmt->fetch();
if (!$event) die("Event not found.");

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Base Update Query
        $sql = "UPDATE event SET eventName = ?, eventDescription = ?, eventDate = ?, eventTime = ?, maxParticipants = ?, 
                venueLocation = ?, eventGeoLocationLat = ?, eventGeoLocationLng = ?";
        
        $params = [
            $_POST['eventName'], 
            $_POST['eventDescription'], 
            $_POST['eventDate'], 
            $_POST['eventTime'], 
            (int)$_POST['maxParticipants'], 
            $_POST['venueLocation'], 
            !empty($_POST['lat']) ? $_POST['lat'] : null, 
            !empty($_POST['lng']) ? $_POST['lng'] : null
        ];

        // Append File Upload logic if a file was selected
        if (!empty($_FILES['poster']['tmp_name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $sql .= ", poster_data = ?, poster_type = ?";
            $params[] = file_get_contents($_FILES['poster']['tmp_name']);
            $params[] = $_FILES['poster']['type'];
        }

        $sql .= " WHERE eventID = ?";
        $params[] = $eventID;

        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute($params);
        header("Location: event_management.php"); exit();
    } catch (PDOException $e) { $error = "Database Error: " . $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Edit Event - FK Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <style>body { font-family: 'Manrope', sans-serif; background-color: #faf8ff; }</style>
</head>
<body class="bg-slate-50 text-slate-900">
<header class="fixed top-0 left-0 right-0 h-[80px] bg-white border-b border-gray-200 flex justify-between items-center px-8 z-50">
    <div class="flex items-center gap-4"><div class="w-10 h-10 bg-blue-600 flex items-center justify-center text-white font-bold rounded">FK</div>
    <h1 class="text-xl font-bold text-slate-800">FK Student Club & Event Management</h1></div>
    <div class="text-right"><p class="text-xs text-slate-500 font-medium">Committee Portal</p><p class="text-sm font-bold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p></div>
</header>

<div class="flex h-screen pt-[80px]">
    <nav class="w-64 fixed left-0 top-[80px] h-[calc(100vh-80px)] border-r border-gray-200 bg-white overflow-y-auto">
        <div class="p-6"><div class="flex items-center gap-2 text-blue-600"><span class="material-icons">groups</span><span class="text-lg font-bold">FK Management</span></div></div>
        <div class="flex flex-col">
            <a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-gray-50" href="dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
            <div class="bg-indigo-50 border-l-4 border-blue-600"><a class="px-6 py-3 flex items-center gap-3 text-blue-700 font-bold" href="event_management.php"><span class="material-icons">build</span> Event Management</a></div>
        </div>
    </nav>

    <main class="ml-64 flex-1 p-8 overflow-y-auto">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded border shadow-sm">
            <h2 class="text-2xl font-extrabold mb-6">Edit Event</h2>
            <?php if ($error): ?><div class="p-4 mb-6 bg-red-100 text-red-800 rounded text-sm"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input name="eventName" value="<?= htmlspecialchars($event['eventName']) ?>" class="w-full border rounded p-2" required placeholder="Event Name">
                <textarea name="eventDescription" class="w-full border rounded p-2" placeholder="Description"><?= htmlspecialchars($event['eventDescription'] ?? '') ?></textarea>
                <div class="grid grid-cols-2 gap-4">
                    <input name="eventDate" type="date" value="<?= htmlspecialchars($event['eventDate']) ?>" class="w-full border rounded p-2" required>
                    <input name="eventTime" type="time" value="<?= htmlspecialchars(date('H:i', strtotime($event['eventTime']))) ?>" class="w-full border rounded p-2">
                </div>
                <input name="venueLocation" value="<?= htmlspecialchars($event['venueLocation']) ?>" class="w-full border rounded p-2" required placeholder="Venue">
                <input name="maxParticipants" type="number" min="1" value="<?= (int)$event['maxParticipants'] ?>" class="w-full border rounded p-2" required placeholder="Max Participants">
                <div class="grid grid-cols-2 gap-4">
                    <input name="lat" value="<?= htmlspecialchars($event['eventGeoLocationLat'] ?? '') ?>" placeholder="Latitude" class="w-full border rounded p-2">
                    <input name="lng" value="<?= htmlspecialchars($event['eventGeoLocationLng'] ?? '') ?>" placeholder="Longitude" class="w-full border rounded p-2">
                </div>
                <label class="block text-sm font-bold text-gray-700">Update Poster (Optional):</label>
                <input name="poster" type="file" class="w-full border rounded p-2">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded font-bold hover:bg-blue-700">Update Event</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>