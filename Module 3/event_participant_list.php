<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '201'; 
    $_SESSION['username'] = 'Ahmad Zaki';
    $_SESSION['role'] = 'Student';
}

$host = 'localhost';
$port = 3307;        
$dbname = 'fk_sc_ems';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

$error = "";
$success = "";
$eventID = trim($_GET['id'] ?? '');

if (empty($eventID)) {
    header("Location: dashboard.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID = ?");
    $stmt->execute([$eventID]);
    $event = $stmt->fetch();
} catch (Exception $e) {
    $error = "Failed to sync resource parameters: " . $e->getMessage();
}

if (!$event) {
    die("Invalid Resource Identifier requested.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $targetRegID = trim($_POST['registration_id'] ?? '');
    if (!empty($targetRegID)) {
        try {
            $deleteStmt = $pdo->prepare("DELETE FROM registration WHERE registrationID = ? AND eventID = ?");
            $deleteStmt->execute([$targetRegID, $eventID]);
            $success = "Participant transaction record successfully removed.";
        } catch (Exception $e) {
            $error = "Transaction processing failure: " . $e->getMessage();
        }
    }
}

$participants = [];
try {
    $partStmt = $pdo->prepare("
        SELECT r.registrationID, r.registrationDate, r.status, r.userID
        FROM registration r
        WHERE r.eventID = ?
        ORDER BY r.registrationDate DESC
    ");
    $partStmt->execute([$eventID]);
    $participants = $partStmt->fetchAll();
} catch (Exception $e) {
    $error = "Failed to synchronize operational roster dataset: " . $e->getMessage();
}

$totalParticipants = count($participants);
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>FK Management - Event Participant List</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#2563eb',
            surface: '#faf8ff',
            'surface-dim': '#d9d9e5',
            'surface-container-low': '#f3f3fe',
            'surface-container-high': '#ececf9',
            'outline-variant': '#e0e0e0',
            'secondary-container': '#e0e7ff',
            danger: '#ef4444',
            'danger-hover': '#dc2626'
          },
          fontFamily: {
            manrope: ['Manrope', 'sans-serif'],
          },
          borderRadius: {
            'round-four': '4px',
          }
        }
      }
    }
  </script>
<style>
    body {
      font-family: 'Manrope', sans-serif;
      background-color: #faf8ff;
    }
  </style>
</head>
<body class="bg-surface text-slate-900">
<header class="fixed top-0 left-0 right-0 h-[80px] bg-white border-b border-outline-variant flex justify-between items-center px-8 z-50">
<div class="flex items-center gap-4">
<div class="w-10 h-10 bg-primary flex items-center justify-center text-white font-bold rounded-round-four">FK</div>
<h1 class="text-xl font-bold text-slate-800">FK Student Club &amp; Event Management</h1>
</div>
<div class="flex items-center gap-6">
<div class="text-right">
<p class="text-xs text-slate-500 font-medium">Student Portal</p>
<p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
</div>
<button class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-round-four hover:bg-surface-container-low transition-all">
<span class="material-icons text-primary">person</span>
<span class="font-bold text-sm">Profile</span>
</button>
</div>
</header>
<div class="flex h-screen pt-[80px]">
<nav class="w-64 fixed left-0 top-[80px] h-[calc(100vh-80px)] border-r border-outline-variant bg-white flex flex-col overflow-y-auto">
<div class="p-6">
<div class="flex items-center gap-2 text-primary mb-1">
<span class="material-icons">groups</span>
<span class="text-lg font-bold">FK Management</span>
</div>
<p class="text-xs text-slate-500 ml-8">Student Portal</p>
</div>
<div class="flex-1">
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="dashboard.php">
<span class="material-icons">dashboard</span>
<span class="text-sm font-medium">Dashboard</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">person</span>
<span class="text-sm font-medium">Profile</span>
</a>
<div class="bg-secondary-container border-l-4 border-primary">
<a class="px-6 py-3 flex items-center gap-3 text-primary" href="#">
<span class="material-icons">people</span>
<span class="text-sm font-bold">Participant List</span>
</a>
</div>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">add_circle</span>
<span class="text-sm font-medium">Create Event</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">how_to_reg</span>
<span class="text-sm font-medium">Create Attendance</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">groups</span>
<span class="text-sm font-medium">Committees</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">qr_code</span>
<span class="text-sm font-medium">Event QR Code</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">assessment</span>
<span class="text-sm font-medium">Report</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">card_membership</span>
<span class="text-sm font-medium">Membership</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">star</span>
<span class="text-sm font-medium">Merit</span>
</a>
<a class="px-6 py-3 flex items-center gap-3 text-slate-600 hover:bg-surface-container-low transition-colors" href="#">
<span class="material-icons">history</span>
<span class="text-sm font-medium">History</span>
</a>
</div>
<div class="mt-auto border-t border-outline-variant">
<button class="w-full px-6 py-4 flex items-center gap-3 text-danger hover:bg-red-50 transition-colors">
<span class="material-icons">logout</span>
<span class="text-sm font-bold">Logout</span>
</button>
</div>
</nav>
<main class="ml-64 flex-1 p-8 overflow-y-auto">
<div class="max-w-6xl mx-auto space-y-6">
<div class="flex items-center gap-2">
<a href="dashboard.php" class="text-primary hover:underline text-sm font-bold flex items-center gap-1">
<span class="material-icons text-sm">arrow_back</span> Dashboard
</a>
</div>

<section class="bg-white border border-outline-variant rounded-round-four p-6 shadow-sm">
<h2 class="text-2xl font-extrabold text-slate-800"><?php echo htmlspecialchars($event['eventName']); ?></h2>
<p class="text-xs text-slate-500 mt-1">Roster Index Summary for Registered Entities</p>
</section>

<?php if (!empty($error)): ?>
    <div class="p-4 bg-red-100 border border-red-200 text-red-800 rounded-round-four text-sm font-medium">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="p-4 bg-green-100 border border-green-200 text-green-800 rounded-round-four text-sm font-medium">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<section class="bg-white p-6 rounded-round-four border border-outline-variant shadow-sm">
<div class="relative max-w-md">
<span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
<input id="search-input" class="w-full pl-10 pr-4 py-2 bg-surface border border-outline-variant rounded-round-four focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Filter Participant Roster by ID Token..." type="text"/>
</div>
</section>

<section class="bg-white border border-outline-variant rounded-round-four overflow-hidden shadow-sm">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-surface border-b border-outline-variant">
<tr>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Registration ID</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">User Token Reference</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Submission Date</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
<th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-center">Action</th>
</tr>
</thead>
<tbody id="participant-table-body" class="divide-y divide-outline-variant">
<?php if ($totalParticipants > 0): ?>
    <?php foreach ($participants as $row): ?>
        <tr class="hover:bg-slate-50 transition-colors searchable-row">
        <td class="px-6 py-4 text-sm font-bold text-slate-800">#REG-<?php echo htmlspecialchars($row['registrationID']); ?></td>
        <td class="px-6 py-4 text-sm text-slate-600 user-id-text">ID-<?php echo htmlspecialchars($row['userID']); ?></td>
        <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M d, Y H:i', strtotime($row['registrationDate'])); ?></td>
        <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-primary">
        <span class="w-1.5 h-1.5 rounded-full bg-primary mr-1.5"></span>
             <?php echo htmlspecialchars($row['status']); ?>
        </span>
        </td>
        <td class="px-6 py-4 text-center">
        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this operational data record entry?');" class="inline">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="registration_id" value="<?php echo htmlspecialchars($row['registrationID']); ?>">
            <button type="submit" class="bg-red-50 text-danger border border-red-200 px-4 py-1.5 rounded-round-four text-xs font-bold hover:bg-danger hover:text-white transition-all">Remove</button>
        </form>
        </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">No transaction allocations logged for this specific event parameter.</td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>
</div>
</main>
</div>

<script>
document.getElementById('search-input').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.searchable-row');
    
    rows.forEach(row => {
        const idText = row.querySelector('.user-id-text').textContent.toLowerCase();
        if (idText.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body></html>