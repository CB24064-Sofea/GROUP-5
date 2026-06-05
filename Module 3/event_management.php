<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = new PDO("mysql:host=localhost;port=3307;dbname=fk_sc_ems;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $pdo->prepare("DELETE FROM event WHERE eventID = ?")->execute([$_POST['eventID']]);
}

$allEvents = $pdo->query("SELECT * FROM event ORDER BY eventDate DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Event Management - FK Management</title>
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
        <div class="text-right"><p class="text-[10px] text-gray-500 uppercase tracking-wider">Student Portal</p><p class="text-sm font-bold">Ahmad Zaki</p></div>
        <button class="flex items-center gap-2 px-4 py-2 border rounded-lg hover:bg-gray-50"><span class="material-icons text-blue-600">person</span> <span class="font-bold text-sm">Profile</span></button>
    </div>
</header>

<div class="flex h-screen pt-[80px]">
    <nav class="w-[280px] fixed left-0 top-[80px] h-[calc(100vh-80px)] border-r border-gray-200 bg-white flex flex-col px-4 py-8">
        <div class="text-blue-600 font-bold mb-8 flex items-center gap-2 px-4"><span class="material-icons">groups</span> FK Management</div>
        <div class="space-y-2 flex-1">
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">dashboard</span> Dashboard</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">person</span> Profile</a>
            <a class="flex items-center gap-4 text-blue-700 bg-blue-50 p-3 rounded-lg font-medium" href="event_management.php"><span class="material-icons">build</span> Event Management</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="create_event.php"><span class="material-icons">add_circle</span> Create Event</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">how_to_reg</span> Create Attendance</a>
            <a class="flex items-center gap-4 text-gray-600 p-3 rounded-lg hover:bg-gray-50" href="#"><span class="material-icons">groups</span> Committees</a>
        </div>
        <a class="text-red-500 flex items-center gap-3 font-bold px-4 pt-4 border-t" href="#"><span class="material-icons">logout</span> Logout</a>
    </nav>

    <main class="ml-[280px] flex-1 p-8">
        <h2 class="text-2xl font-bold text-slate-800 mb-8">Event Operations Control</h2>
        
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm mb-8 flex justify-between items-center">
            <div class="relative w-1/2">
                <span class="material-icons absolute left-3 top-3 text-gray-400">search</span>
                <input id="search-input" type="text" placeholder="Search Master Catalog Records..." class="w-full pl-10 pr-4 py-3 border rounded-lg bg-gray-50">
            </div>
            <a href="create_event.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold flex items-center gap-2 hover:bg-blue-700">
                <span class="material-icons">add</span> Add New Event
            </a>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-gray-500 uppercase text-[11px] tracking-widest font-bold">
                        <th class="p-5">Event</th>
                        <th class="p-5">Execution Date</th>
                        <th class="p-5">Location</th>
                        <th class="p-5">Status</th>
                        <th class="p-5">Operational Links</th>
                    </tr>
                </thead>
                <tbody id="management-table-body">
                    <?php foreach ($allEvents as $e): ?>
                    <tr class="border-b hover:bg-gray-50 searchable-row">
                        <td class="p-5 font-bold event-name-text"><?= htmlspecialchars($e['eventName']) ?></td>
                        <td class="p-5"><?= htmlspecialchars($e['eventDate']) ?></td>
                        <td class="p-5"><?= htmlspecialchars($e['venueLocation']) ?></td>
                        <td class="p-5"><span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Open</span></td>
                        <td class="p-5 flex gap-4 text-gray-400">
                            <a href="#"><span class="material-icons text-sm">visibility</span></a>
                            <a href="edit_event.php?id=<?= $e['eventID'] ?>"><span class="material-icons text-sm">edit</span></a>
                            <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="eventID" value="<?= $e['eventID'] ?>"><button><span class="material-icons text-sm text-red-500">delete</span></button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
document.getElementById('search-input').addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.searchable-row').forEach(row => {
        row.style.display = row.querySelector('.event-name-text').textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>