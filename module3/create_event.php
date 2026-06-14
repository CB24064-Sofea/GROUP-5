<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DYNAMIC PATH RESOLUTION TO PREVENT 404 / FILE NOT FOUND ERRORS ---
// This checks both the parent directory and sibling module directories for auth and config files
$baseDir = __DIR__;
$authPath = '';
$configPath = '';

$possiblePaths = [
    '/../auth.php' => '/../config.php',
    '/../module1/auth.php' => '/../module1/config.php',
    '/auth.php' => '/config.php'
];

foreach ($possiblePaths as $aP => $cP) {
    if (file_exists($baseDir . $aP)) {
        $authPath = $baseDir . $aP;
        $configPath = $baseDir . $cP;
        break;
    }
}

if ($authPath && $configPath) {
    require_once $authPath;
    require_once $configPath;
} else {
    die("Critical Error: Core system files (auth.php / config.php) could not be located. Please verify your folder structure.");
}

/*
|--------------------------------------------------------------------------
| Committee / Admin Only
|--------------------------------------------------------------------------
*/
requireCommitteeOrAdmin();

$errorMessage = '';
$committeeClubID = getCommitteeClubID();
$clubs = [];

/*
|--------------------------------------------------------------------------
| Load Clubs
|--------------------------------------------------------------------------
*/
if (isAdmin()) {
    $result = $conn->query("
        SELECT clubID, clubName
        FROM club
        ORDER BY clubName ASC
    ");
    if ($result) {
        $clubs = $result->fetch_all(MYSQLI_ASSOC);
    }
} else {
    $stmt = $conn->prepare("
        SELECT clubID, clubName
        FROM club
        WHERE clubID = ?
    ");
    $stmt->bind_param("i", $committeeClubID);
    $stmt->execute();
    $clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Create Event Form Post Execution Block
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $clubID = isAdmin() ? (int)$_POST['clubID'] : (int)$committeeClubID;
        $userID = getUserID();

        $eventName = trim($_POST['eventName']);
        $eventDescription = trim($_POST['eventDescription']);
        $eventDate = trim($_POST['eventDate']);
        $eventTime = trim($_POST['eventTime']);
        $venueLocation = trim($_POST['venueLocation']);
        $maxParticipants = (int)$_POST['maxParticipants'];

        $registrationDeadline = str_replace('T', ' ', $_POST['registrationDeadline']) . ':00';
        $eventStatus = trim($_POST['eventStatus']);

        $lat = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : 0;
        $lng = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : 0;

        if (
            empty($clubID) || empty($userID) || empty($eventName) || 
            empty($eventDescription) || empty($eventDate) || empty($eventTime) || 
            empty($venueLocation) || $maxParticipants < 1
        ) {
            throw new Exception("Please complete all required fields.");
        }
        
        if ($lat == 0 || $lng == 0) {
            throw new Exception("Please select a location coordinates pin point on the map tool.");
        }

        $stmt = $conn->prepare("
            INSERT INTO event (
                clubID, userID, eventName, eventDescription, eventDate, eventTime, 
                venueLocation, maxParticipants, registrationDeadline, eventStatus, 
                eventGeoLocationLat, eventGeoLocationLog, createAt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            throw new Exception("Prepare Error: " . $conn->error);
        }

        $stmt->bind_param(
            "issssssissdd",
            $clubID, $userID, $eventName, $eventDescription, $eventDate, $eventTime,
            $venueLocation, $maxParticipants, $registrationDeadline, $eventStatus, $lat, $lng
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute Error: " . $stmt->error);
        }

        $stmt->close();
        $_SESSION['successMessage'] = "Event created successfully.";
        header("Location: event_management.php");
        exit();

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - FK Portal</title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .event-form label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }
        .event-form input,
        .event-form textarea,
        .event-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .event-form textarea { resize: none; }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        /* Unified Leaflet Map Container CSS UI Layout Framework */
        .map-wrapper {
            margin-top: 10px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
            background: #f8fafc;
        }
        .map-search-bar {
            display: flex;
            padding: 8px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            gap: 6px;
        }
        #map {
            height: 300px;
            width: 100%;
            z-index: 1;
        }
        .publish-btn {
            width: 100%;
            background: #082b63;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }
        .publish-btn:hover { background: #0b3a82; }
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 12px;
            color: #1d4ed8;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <?php 
    // Dynamically safely resolving header include assets paths
    if (file_exists($baseDir . '/../topbar.php')) {
        include $baseDir . '/../topbar.php';
    } else {
        include $baseDir . '/../module1/topbar.php';
    }
    ?>

    <div class="app-container">
        <?php 
        if (file_exists($baseDir . '/../sidebar.php')) {
            include $baseDir . '/../sidebar.php';
        } else {
            include $baseDir . '/../module1/sidebar.php';
        }
        ?>

        <main class="main-content">
            <div style="max-width:650px; margin:0 auto;">
                <div style="background:#ffffff; border-radius:8px; padding:25px; box-shadow:0 2px 8px rgba(0,0,0,.08);">

                    <h2 style="text-align:center; color:#0f2d5c; margin-bottom:5px; font-size:24px; font-weight:700;">
                        Create New Club Event
                    </h2>
                    <p style="text-align:center; color:#64748b; font-size:12px; margin-bottom:25px;">
                        Fill in the details below to publish a new event to the student portal.
                    </p>

                    <?php if ($errorMessage): ?>
                        <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-size: 13px;">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="create_event.php">
                        <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">

                        <div class="event-form">
                            <label>Event Name</label>
                            <input type="text" name="eventName" value="<?= htmlspecialchars($_POST['eventName'] ?? '') ?>" required>
                            <br><br>

                            <label>Description</label>
                            <textarea name="eventDescription" rows="4" required><?= htmlspecialchars($_POST['eventDescription'] ?? '') ?></textarea>
                            <br>

                            <div class="form-row">
                                <div>
                                    <label>Event Date</label>
                                    <input type="date" name="eventDate" value="<?= htmlspecialchars($_POST['eventDate'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label>Max Seats</label>
                                    <input type="number" name="maxParticipants" min="1" value="<?= htmlspecialchars($_POST['maxParticipants'] ?? '100') ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div>
                                    <label>Event Time</label>
                                    <input type="time" name="eventTime" value="<?= htmlspecialchars($_POST['eventTime'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label>Registration Closing Deadline</label>
                                    <input type="datetime-local" name="registrationDeadline" required>
                                </div>
                            </div>

                            <label>Venue Location Name</label>
                            <div style="display:flex; gap:8px; align-items:center; margin-bottom: 5px;">
                                <input type="text" id="venueLocation" name="venueLocation" placeholder="e.g. Dewan Kuliah 1" value="<?= htmlspecialchars($_POST['venueLocation'] ?? '') ?>" required>
                                <button type="button" id="locateMeBtn" style="white-space:nowrap; border:1px solid #d1d5db; background:white; padding:10px; border-radius:4px; cursor:pointer; font-weight: 500;">
                                    📍 Pin My Location
                                </button>
                            </div>

                            <div class="map-wrapper">
                                <div class="map-search-bar">
                                    <input type="text" id="mapSearchInput" placeholder="Search campus landmarks, building names..." style="flex:1; padding: 6px 10px; border: 1px solid #cbd5e1; font-size: 13px; border-radius:4px;">
                                    <button type="button" id="mapSearchBtn" style="padding:6px 12px; background:#475569; color:white; border:none; border-radius:4px; cursor:pointer; font-size:13px;">Search</button>
                                </div>
                                <div id="map"></div>
                            </div>
                            <p style="margin-top:-5px; margin-bottom:15px; color:#64748b; font-size: 12px;">
                                Click on the map, use Search, or click "Pin My Location" to establish coordinates. You can drag the marker pin freely.
                            </p>

                            <label>Host Club</label>
                            <select name="clubID" <?= isCommittee() ? 'disabled' : '' ?> required>
                                <?php foreach($clubs as $club): ?>
                                    <option value="<?= $club['clubID'] ?>" <?= (!isAdmin() && $club['clubID'] == $committeeClubID) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($club['clubName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if(isCommittee()): ?>
                                <input type="hidden" name="clubID" value="<?= $committeeClubID ?>">
                            <?php endif; ?>
                            <br><br>

                            <label>Registration Status</label>
                            <select name="eventStatus" required>
                                <option value="Open">Open (Accepting Registrations)</option>
                                <option value="Closed">Closed</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <br><br>

                            <button type="submit" class="publish-btn">🚀 Publish Event</button>
                            <a href="manage_event.php" class="cancel-link">Cancel and Return</a>
                        </div>
                    </form>

                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Setup initial default center viewing coordinates
        var defaultLat = 3.5469;
        var defaultLng = 103.4228;

        var savedLat = document.getElementById('latitude').value;
        var savedLng = document.getElementById('longitude').value;

        var startLat = savedLat ? parseFloat(savedLat) : defaultLat;
        var startLng = savedLng ? parseFloat(savedLng) : defaultLng;

        var map = L.map('map').setView([startLat, startLng], 16);

        // OpenStreetMap Layer integration
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker = null;

        // Render pin on form reloads if coordinates value parameters are active
        if (savedLat && savedLng) {
            createMarker(startLat, startLng);
        }

        function createMarker(lat, lng) {
            if (marker) {
                map.removeLayer(marker);
            }
            // Marker is set to draggable:true so users can fine-tune positioning
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            updateHiddenFields(lat, lng);

            marker.on('dragend', function (e) {
                var position = marker.getLatLng();
                updateHiddenFields(position.lat, position.lng);
            });
        }

        function updateHiddenFields(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        }

        // Click directly on the map workspace to place the coordinate pin
        map.on('click', function(e){
            createMarker(e.latlng.lat, e.latlng.lng);
        });

        // Device location tracking engine mapping handler
        document.getElementById('locateMeBtn').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var currentLat = position.coords.latitude;
                    var currentLng = position.coords.longitude;
                    map.setView([currentLat, currentLng], 17);
                    createMarker(currentLat, currentLng);
                }, function() {
                    alert("Could not access your device location. Please manually drop a pin or try searching.");
                });
            } else {
                alert("Your browser does not support local device location geolocation lookups.");
            }
        });

        // Landmark text querying engine search processor handler
        function performSearch() {
            var query = document.getElementById('mapSearchInput').value;
            if(!query.trim()) return;

            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if(data && data.length > 0) {
                        var targetLat = parseFloat(data[0].lat);
                        var targetLng = parseFloat(data[0].lon);
                        map.setView([targetLat, targetLng], 17);
                        createMarker(targetLat, targetLng);
                    } else {
                        alert("Location landmark not found. Please try clarifying your landmark query words.");
                    }
                })
                .catch(err => {
                    console.error("Geocoding fetch operation error: ", err);
                });
        }

        document.getElementById('mapSearchBtn').addEventListener('click', performSearch);
        document.getElementById('mapSearchInput').addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    </script>
</body>
</html>