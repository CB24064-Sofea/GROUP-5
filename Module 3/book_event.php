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
    header("Location: my_event_registration.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID = ?");
    $stmt->execute([$eventID]);
    $event = $stmt->fetch();
} catch (Exception $e) {
    $error = "Failed to sync resource details: " . $e->getMessage();
}

if (!$event) {
    die("Invalid Resource Identifier requested.");
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE eventID = ? AND status = 'Registered'");
$countStmt->execute([$eventID]);
$registeredCount = (int)$countStmt->fetchColumn();
$maxVal = (int)$event['maxParticipants'];

$userCheck = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE eventID = ? AND userID = ?");
$userCheck->execute([$eventID, $_SESSION['user_id']]);
$isAlreadyBooked = $userCheck->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_booking') {
    if ($isAlreadyBooked) {
        $error = "Our logs show you have already secured a slot reservation for this activity.";
    } elseif ($maxVal > 0 && $registeredCount >= $maxVal) {
        $error = "Booking denied. This event has officially reached its structural capacity limit.";
    } else {
        try {
            $bookStmt = $pdo->prepare("INSERT INTO registration (eventID, userID, status, registrationDate) VALUES (?, ?, 'Registered', NOW())");
            $bookStmt->execute([$eventID, $_SESSION['user_id']]);
            
            $success = "Your booking request has been approved! Seat allocation secured.";
            $isAlreadyBooked = true;
            $registeredCount++;
        } catch (Exception $e) {
            $error = "Transaction processing system error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>FK Management - Book Event Slot</title>
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
<span class="material-icons">bookmark_add</span>
<span class="text-sm font-bold">Book Slot</span>
</a>
<div class="pl-14 pb-2 flex flex-col gap-2">
<a class="text-xs text-slate-500 hover:text-primary transition-colors" href="upcoming_events.php">Upcoming Event</a>
<a class="text-xs text-slate-500 hover:text-primary transition-colors" href="my_registrations.php">My Registration</a>
</div>
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
<div class="max-w-3xl mx-auto space-y-6">
<div class="flex items-center gap-2">
<a href="my_event_registration.php" class="text-primary hover:underline text-sm font-bold flex items-center gap-1">
<span class="material-icons text-sm">arrow_back</span> Return to Desk
</a>
</div>

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

<section class="bg-white border border-outline-variant rounded-round-four p-8 shadow-sm space-y-6">
<div class="border-b border-outline-variant pb-6 text-center">
<span class="material-icons text-4xl text-primary mb-2">confirmation_number</span>
<h2 class="text-2xl font-extrabold text-slate-800">Verify Ticket Selection</h2>
<p class="text-xs text-slate-500 mt-1">Please confirm your selection details below before finalizing system database logging</p>
</div>

<div class="bg-surface p-6 border border-outline-variant rounded-round-four space-y-4">
<div class="flex justify-between items-center border-b border-dashed border-outline-variant pb-3">
<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Target Application</span>
<span class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($event['eventName']); ?></span>
</div>
<div class="flex justify-between items-center border-b border-dashed border-outline-variant pb-3">
<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned Venue</span>
<span class="text-sm text-slate-700 font-medium"><?php echo htmlspecialchars($event['venueLocation']); ?></span>
</div>
<div class="flex justify-between items-center border-b border-dashed border-outline-variant pb-3">
<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Target Timeline</span>
<span class="text-sm text-slate-700 font-medium">
    <?php echo date('F d, Y', strtotime($event['eventDate'])); ?> @ <?php echo date('h:i A', strtotime($event['eventTime'])); ?>
</span>
</div>
<div class="flex justify-between items-center">
<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Student Profile Holder</span>
<span class="text-sm text-slate-800 font-bold"><?php echo htmlspecialchars($_SESSION['username']); ?> (ID-<?php echo htmlspecialchars($_SESSION['user_id']); ?>)</span>
</div>
</div>

<div class="pt-4 flex justify-end gap-3">
<a href="my_event_registration.php" class="bg-slate-100 text-slate-600 border border-outline-variant px-6 py-2 rounded-round-four text-sm font-bold hover:bg-slate-200 transition-all">Dismiss</a>
<?php if ($isAlreadyBooked): ?>
    <button class="bg-gray-200 text-gray-400 font-bold px-6 py-2 rounded-round-four text-sm cursor-not-allowed" disabled>Slot Booked</button>
<?php elseif ($maxVal > 0 && $registeredCount >= $maxVal): ?>
    <button class="bg-slate-100 text-slate-300 font-bold px-6 py-2 rounded-round-four text-sm cursor-not-allowed" disabled>Capacity Exhausted</button>
<?php else: ?>
    <form method="POST" action="">
        <input type="hidden" name="action" value="confirm_booking">
        <button type="submit" class="bg-primary text-white font-bold px-6 py-2 rounded-round-four text-sm hover:bg-blue-700 transition-all shadow-sm">Confirm Booking Slot</button>
    </form>
<?php endif; ?>
</div>
</section>
</div>
</main>
</div>
</body></html>