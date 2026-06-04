<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulated active session variables for the Student user
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = '201'; 
    $_SESSION['username'] = 'Ahmad Zaki';
    $_SESSION['role'] = 'Student';
}

// Database Connection configuration
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

// Handle Registration Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $targetEventID = trim($_POST['event_id'] ?? '');
    
    if (!empty($targetEventID)) {
        try {
            // Check if already registered
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE eventID = ? AND userID = ?");
            $checkStmt->execute([$targetEventID, $_SESSION['user_id']]);
            $alreadyRegistered = $checkStmt->fetchColumn() > 0;

            if ($alreadyRegistered) {
                $error = "You have already submitted a registration request for this event.";
            } else {
                // Verify capacity limits if applicable
                $eventStmt = $pdo->prepare("SELECT maxParticipants FROM event WHERE eventID = ?");
                $eventStmt->execute([$targetEventID]);
                $eventData = $eventStmt->fetch();

                if ($eventData) {
                    $max = (int)$eventData['maxParticipants'];
                    
                    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE eventID = ? AND status = 'Registered'");
                    $countStmt->execute([$targetEventID]);
                    $currentCount = $countStmt->fetchColumn();

                    if ($max > 0 && $currentCount >= $max) {
                        $error = "Registration failure: Maximum participant limit has been reached.";
                    } else {
                        // Create a new registration entry
                        $regStmt = $pdo->prepare("INSERT INTO registration (eventID, userID, status, registrationDate) VALUES (?, ?, 'Registered', NOW())");
                        $regStmt->execute([$targetEventID, $_SESSION['user_id']]);
                        $success = "Registration successfully processed for selected event resource.";
                    }
                } else {
                    $error = "Target event entity could not be verified inside repository.";
                }
            }
        } catch (Exception $e) {
            $error = "Transaction handling processing error: " . $e->getMessage();
        }
    }
}

// Fetch all open or published events from the repository
$events = [];
try {
    $fetchStmt = $pdo->prepare("SELECT * FROM event WHERE eventStatus IN ('Open', 'Published') ORDER BY eventDate ASC");
    $fetchStmt->execute();
    $events = $fetchStmt->fetchAll();
} catch (Exception $e) {
    $error = "Failed to synchronize operational event view records: " . $e->getMessage();
}

// Dynamic counter for stats card
$totalActiveEvents = count($events);
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Upcoming Event List - FK Student Club & Event Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-surface-variant": "#434655",
                        "tertiary-fixed": "#ffddb8",
                        "primary-fixed": "#dbe1ff",
                        "on-surface": "#191b23",
                        "surface-container": "#ededf9",
                        "surface-container-highest": "#e1e2ed",
                        "error": "#ba1a1a",
                        "on-secondary-fixed": "#0b1c30",
                        "on-tertiary-container": "#ffeedd",
                        "on-tertiary": "#ffffff",
                        "inverse-surface": "#2e3039",
                        "on-tertiary-fixed": "#2a1700",
                        "outline-variant": "#c3c6d7",
                        "on-primary": "#ffffff",
                        "surface-bright": "#faf8ff",
                        "primary": "#004ac6",
                        "tertiary-fixed-dim": "#ffb95f",
                        "surface-tint": "#0053db",
                        "surface-container-low": "#f3f3fe",
                        "secondary-fixed-dim": "#b7c8e1",
                        "on-secondary": "#ffffff",
                        "on-error-container": "#93000a",
                        "secondary-fixed": "#d3e4fe",
                        "on-error": "#ffffff",
                        "secondary-container": "#d0e1fb",
                        "surface": "#faf8ff",
                        "surface-container-high": "#e7e7f3",
                        "tertiary-container": "#996100",
                        "outline": "#737686",
                        "on-tertiary-fixed-variant": "#653e00",
                        "error-container": "#ffdad6",
                        "on-primary-fixed": "#00174b",
                        "secondary": "#505f76",
                        "primary-fixed-dim": "#b4c5ff",
                        "on-secondary-fixed-variant": "#38485d",
                        "on-primary-container": "#eeefff",
                        "on-primary-fixed-variant": "#003ea8",
                        "surface-container-lowest": "#ffffff",
                        "tertiary": "#784b00",
                        "inverse-primary": "#b4c5ff",
                        "primary-container": "#2563eb",
                        "on-background": "#191b23",
                        "surface-variant": "#e1e2ed",
                        "background": "#faf8ff",
                        "surface-dim": "#d9d9e5",
                        "on-secondary-container": "#54647a",
                        "inverse-on-surface": "#f0f0fb"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "sidebar-width": "280px",
                        "header-height": "80px",
                        "stack-gap": "1.5rem",
                        "inline-gap": "0.75rem",
                        "table-cell-padding": "1rem 1.5rem",
                        "container-padding": "2rem"
                    },
                    "fontFamily": {
                        "headline-md": ["Manrope"],
                        "headline-lg": ["Manrope"],
                        "label-md": ["Manrope"],
                        "body-md": ["Manrope"],
                        "title-lg": ["Manrope"],
                        "body-sm": ["Manrope"]
                    },
                    "fontSize": {
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "headline-lg": ["30px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "title-lg": ["18px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
                    }
                },
            },
        }
    </script>
<style>
        body { font-family: 'Manrope', sans-serif; background-color: #F8FAFC; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .active-sidebar-item { background-color: #d0e1fb; color: #004ac6; border-left: 4px solid #004ac6; font-weight: 700; }
        .sidebar-item-hover:hover { background-color: #f3f3fe; }
        .table-row-hover:hover { background-color: #f1f5f9; transition: background-color 0.2s; }
        .custom-shadow { box-shadow: 0px 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="text-on-surface">
<header class="flex justify-between items-center h-[80px] px-8 w-full bg-surface border-b border-outline-variant fixed top-0 left-0 z-50">
<div class="flex items-center gap-4">
<div class="w-10 h-10 bg-primary flex items-center justify-center rounded-lg text-white font-bold">FK</div>
<h1 class="text-title-lg font-title-lg font-bold text-on-surface">FK Student Club & Event Management</h1>
</div>
<div class="flex items-center gap-6">
<div class="text-right hidden md:block">
<p class="text-label-md font-label-md text-outline">Student Portal</p>
<p class="text-body-md font-body-md font-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
</div>
<div class="flex items-center gap-3 bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant">
<span class="material-symbols-outlined text-primary">person</span>
<span class="text-label-md font-label-md font-bold">Profile</span>
</div>
</div>
</header>
<div class="flex pt-[80px]">
<aside class="fixed left-0 top-0 h-full w-[280px] border-r border-outline-variant bg-surface flex flex-col pt-[80px] z-40">
<div class="p-6">
<div class="flex items-center gap-3 mb-8">
<span class="material-symbols-outlined text-primary" style="font-size: 32px;">groups</span>
<div>
<h2 class="text-headline-md font-headline-md text-primary">FK Management</h2>
<p class="text-body-sm font-body-sm text-secondary">Student Dashboard</p>
</div>
</div>
<nav class="flex flex-col gap-1">
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-body-md font-body-md">Dashboard</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">person</span>
<span class="text-body-md font-body-md">Profile</span>
</a>
<a class="active-sidebar-item px-4 py-3 flex items-center gap-3 rounded-r-lg transition-all" href="#">
<span class="material-symbols-outlined">event</span>
<span class="text-body-md font-body-md">My Events</span>
</a>
<div class="ml-4 flex flex-col gap-1 border-l border-outline-variant mt-1 mb-2">
<a class="pl-6 py-2 text-primary font-bold text-body-sm" href="upcoming_events.php">Upcoming Event</a>
<a class="pl-6 py-2 text-secondary text-body-sm hover:text-primary transition-colors" href="#">My Registration</a>
</div>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">add_circle</span>
<span class="text-body-md font-body-md">Create Event</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">how_to_reg</span>
<span class="text-body-md font-body-md">Create Attendance</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">groups</span>
<span class="text-body-md font-body-md">Committees</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">qr_code</span>
<span class="text-body-md font-body-md">Event QR Code</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">assessment</span>
<span class="text-body-md font-body-md">Report</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">card_membership</span>
<span class="text-body-md font-body-md">Membership</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">star</span>
<span class="text-body-md font-body-md">Merit</span>
</a>
<a class="sidebar-item-hover text-secondary px-4 py-3 flex items-center gap-3 rounded-lg transition-all" href="#">
<span class="material-symbols-outlined">history</span>
<span class="text-body-md font-body-md">History</span>
</a>
</nav>
</div>
<div class="mt-auto p-6 border-t border-outline-variant">
<button class="w-full sidebar-item-hover text-error px-4 py-3 flex items-center gap-3 rounded-lg transition-all text-left">
<span class="material-symbols-outlined">logout</span>
<span class="text-body-md font-body-md">Logout</span>
</button>
</div>
</aside>
<main class="ml-[280px] flex-1 p-[2rem] bg-background min-h-screen">
<div class="max-w-7xl mx-auto">
<div class="mb-10 relative h-64 rounded-xl overflow-hidden custom-shadow group">
<img alt="Tech Events Hero" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtNAEalte3ruhYiq8I1sk6_Wq2UvrbGnkngeKTZcEZJCOpIrrf7w6CVViu-8_Hu1C4dNL02CKfoOY_EHZWtNCxZvKDRLxDVE6e7RgLkA5SiwO4VGkUpTOKKjtr03B5LaRhCuPxvhYvaJhB3KZdmoeNQGkTuy_UiE0qP55IAvvZQlemsZAqEt3382Q2vxdcMCBQM0HofuAgLaqboJ84a6vzKeJ-Si8_dpne_Xy7r8OJ3HHIM_S5RgL2Dwx1vTyL7hg1e3Disy0eB3rE"/>
<div class="absolute inset-0 bg-gradient-to-r from-primary/80 to-transparent flex items-center px-12">
<div class="text-white">
<h2 class="text-headline-lg font-headline-lg mb-2">Upcoming Event List</h2>
<p class="text-body-md opacity-90">Discover and register for the latest technology workshops and club activities.</p>
</div>
</div>
</div>

<?php if (!empty($error)): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-800 rounded-xl text-sm font-medium">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-xl text-sm font-medium">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
<div class="md:col-span-3 bg-surface border border-outline-variant p-6 rounded-xl custom-shadow flex items-center gap-4">
<div class="flex-1 relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
<input id="search-input" class="w-full pl-12 pr-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-body-md" placeholder="Search Events" type="text"/>
</div>
<button class="bg-primary text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition-all flex items-center gap-2">
<span class="material-symbols-outlined">filter_list</span>
                            Filter
                        </button>
</div>
<div class="bg-primary-container text-on-primary-container p-6 rounded-xl custom-shadow flex flex-col justify-center items-center text-center">
<p class="text-headline-md font-headline-md"><?php echo $totalActiveEvents; ?></p>
<p class="text-label-md font-label-md uppercase tracking-wider">Active Events</p>
</div>
</div>
<div class="bg-surface border border-outline-variant rounded-xl overflow-hidden custom-shadow">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low">
<th class="px-6 py-4 text-label-md font-label-md text-on-surface-variant uppercase tracking-wider border-b border-outline-variant">Event Name</th>
<th class="px-6 py-4 text-label-md font-label-md text-on-surface-variant uppercase tracking-wider border-b border-outline-variant">Date & Time</th>
<th class="px-6 py-4 text-label-md font-label-md text-on-surface-variant uppercase tracking-wider border-b border-outline-variant">Location</th>
<th class="px-6 py-4 text-label-md font-label-md text-on-surface-variant uppercase tracking-wider border-b border-outline-variant text-center">Max Participants</th>
<th class="px-6 py-4 text-label-md font-label-md text-on-surface-variant uppercase tracking-wider border-b border-outline-variant">Status</th>
<th class="px-6 py-4 text-label-md font-label-md text-on-surface-variant uppercase tracking-wider border-b border-outline-variant text-right">Action</th>
</tr>
</thead>
<tbody id="events-table-body" class="divide-y divide-outline-variant">
<?php if (count($events) > 0): ?>
    <?php foreach ($events as $row): ?>
        <?php 
            // Calculate current total registrations to determine status indicators dynamically
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE eventID = ? AND status = 'Registered'");
            $countStmt->execute([$row['eventID']]);
            $registeredCount = (int)$countStmt->fetchColumn();

            $maxVal = (int)$row['maxParticipants'];
            
            // Check if user is already registered for this row
            $userCheck = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE eventID = ? AND userID = ?");
            $userCheck->execute([$row['eventID'], $_SESSION['user_id']]);
            $isUserRegistered = $userCheck->fetchColumn() > 0;

            // Status Badge rendering configurations
            $statusLabel = "Open";
            $statusClasses = "bg-green-100 text-green-700";
            $dotClasses = "bg-green-500";
            $disableBtn = false;

            if ($maxVal > 0 && $registeredCount >= $maxVal) {
                $statusLabel = "Full";
                $statusClasses = "bg-gray-100 text-gray-500";
                $dotClasses = "bg-gray-400";
                $disableBtn = true;
            } elseif ($maxVal > 0 && ($registeredCount / $maxVal) >= 0.8) {
                $statusLabel = "Filling Fast";
                $statusClasses = "bg-amber-100 text-amber-700";
                $dotClasses = "bg-amber-500";
            }
        ?>
        <tr class="table-row-hover searchable-row">
        <td class="px-6 py-6">
        <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded bg-tertiary-container flex items-center justify-center text-on-tertiary-container">
        <span class="material-symbols-outlined">precision_manufacturing</span>
        </div>
        <span class="text-body-md font-bold event-name-text"><?php echo htmlspecialchars($row['eventName']); ?></span>
        </div>
        </td>
        <td class="px-6 py-6">
        <div class="text-body-md"><?php echo !empty($row['eventDate']) ? date('M d, Y', strtotime($row['eventDate'])) : 'N/A'; ?></div>
        <div class="text-body-sm text-outline"><?php echo !empty($row['eventTime']) ? date('h:i A', strtotime($row['eventTime'])) : 'All Day'; ?></div>
        </td>
        <td class="px-6 py-6 text-body-md"><?php echo htmlspecialchars($row['venueLocation'] ?? 'N/A'); ?></td>
        <td class="px-6 py-6 text-center text-body-md"><?php echo $maxVal > 0 ? $maxVal : 'Unlimited'; ?></td>
        <td class="px-6 py-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-md font-bold <?php echo $statusClasses; ?>">
        <span class="w-2 h-2 rounded-full <?php echo $dotClasses; ?>"></span>
                                    <?php echo $statusLabel; ?>
                                </span>
        </td>
        <td class="px-6 py-6 text-right">
        <?php if ($isUserRegistered): ?>
            <button class="bg-gray-200 text-gray-500 font-bold px-5 py-2 rounded-lg text-body-sm cursor-not-allowed" disabled>Registered</button>
        <?php elseif ($disableBtn): ?>
            <button class="bg-surface-container-highest text-outline font-bold px-5 py-2 rounded-lg text-body-sm cursor-not-allowed" disabled>Full</button>
        <?php else: ?>
            <form method="POST" action="" class="inline">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($row['eventID']); ?>">
                <button type="submit" class="bg-primary-container text-primary font-bold px-5 py-2 rounded-lg text-body-sm hover:bg-primary hover:text-white transition-all custom-shadow">Register</button>
            </form>
        <?php endif; ?>
        </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="px-6 py-12 text-center text-outline text-body-md">No currently available or upcoming public events are listed.</td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</div>
<div class="p-6 bg-surface-container-low border-t border-outline-variant flex justify-between items-center">
<span class="text-body-sm text-outline">Showing <?php echo count($events); ?> active listings</span>
<div class="flex gap-2">
<button class="w-10 h-10 rounded-lg border border-outline-variant flex items-center justify-center hover:bg-surface-container-high transition-all">
<span class="material-symbols-outlined">chevron_left</span>
</button>
<button class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center">1</button>
<button class="w-10 h-10 rounded-lg border border-outline-variant flex items-center justify-center hover:bg-surface-container-high transition-all">
<span class="material-symbols-outlined">chevron_right</span>
</button>
</div>
</div>
</div>
</div>
</main>
</div>

<script>
document.getElementById('search-input').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.searchable-row');
    
    rows.forEach(row => {
        const nameText = row.querySelector('.event-name-text').textContent.toLowerCase();
        if (nameText.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body></html>