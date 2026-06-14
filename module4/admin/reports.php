<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

if (!isAdmin()) {
    header("Location: /GROUP%205/module1/login.php");
    exit();
}

$page_title = "Reports";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <link rel="stylesheet" href="/GROUP%205/standard.css">

    <style>
        .action-buttons{
            margin-bottom:20px;
            display:flex;
            gap:10px;
        }

        .btn-report{
            background:#0284c7;
            color:white;
            padding:10px 18px;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-weight:600;
            text-decoration:none;
        }

        .btn-report:hover{
            opacity:0.9;
        }

        .btn-csv{
            background:#16a34a;
        }

        /* PRINT MODE */
        @media print {

            .sidebar,
            .app-sidebar,
            nav,
            .topbar,
            .action-buttons,
            button,
            a {
                display:none !important;
            }

            body{
                margin:0;
                padding:0;
                background:#fff;
            }

            .app-container{
                display:block !important;
            }

            .main-content{
                width:100% !important;
                margin:0 !important;
                padding:10px !important;
            }

            .form-card-container{
                border:none !important;
                box-shadow:none !important;
                page-break-inside:avoid;
            }

            table{
                width:100% !important;
                border-collapse:collapse !important;
            }

            table th,
            table td{
                border:1px solid #000 !important;
                padding:8px !important;
                color:#000 !important;
            }

            h1,h2,h3{
                color:#000 !important;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../../topbar.php'; ?>

<div class="app-container">

    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="main-content">

        <h1 class="page-title">Reports & Analytics</h1>

        <div class="action-buttons">
            <button class="btn-report" onclick="window.print()">
                Print Report
            </button>

        </div>

        <div id="report-content">

            <!-- MOST ACTIVE CLUBS -->
            <div class="form-card-container">

                <h2>Most Active Clubs</h2>

                <?php
                $clubReport = $conn->query("
                    SELECT
                        c.clubName,
                        COUNT(e.eventID) AS total_events
                    FROM club c
                    LEFT JOIN event e
                        ON c.clubID = e.clubID
                    GROUP BY c.clubID
                    ORDER BY total_events DESC
                    LIMIT 5
                ");
                ?>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Club Name</th>
                        <th>Total Events Organized</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php while($row = $clubReport->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['clubName']) ?></td>
                            <td><?= (int)$row['total_events'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>

            </div>

            <!-- EVENT ATTENDANCE SUMMARY -->
            <div class="form-card-container">

                <h2>Event Attendance Summary</h2>

                <?php
                $joinReport = $conn->query("
                    SELECT
                        e.eventName,
                        COUNT(er.registrationID) AS registered,

                        SUM(
                            CASE
                                WHEN a.attendanceStatus='Present'
                                THEN 1
                                ELSE 0
                            END
                        ) AS present,

                        SUM(
                            CASE
                                WHEN a.attendanceStatus='Late'
                                THEN 1
                                ELSE 0
                            END
                        ) AS late,

                        SUM(
                            CASE
                                WHEN a.attendanceStatus='Absent'
                                THEN 1
                                ELSE 0
                            END
                        ) AS absent

                    FROM event e

                    LEFT JOIN event_registration er
                        ON e.eventID = er.eventID
                        AND er.registrationStatus='Success'

                    LEFT JOIN attendance a
                        ON er.registrationID = a.registrationID

                    GROUP BY e.eventID
                    ORDER BY e.eventDate DESC
                ");
                ?>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Registered</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Attendance Rate (%)</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php while($row = $joinReport->fetch_assoc()): ?>

                        <?php
                        $registered = (int)$row['registered'];
                        $present = (int)$row['present'];
                        $late = (int)$row['late'];

                        $attendanceRate =
                            $registered > 0
                            ? round((($present + $late) / $registered) * 100, 2)
                            : 0;
                        ?>

                        <tr>
                            <td><?= htmlspecialchars($row['eventName']) ?></td>
                            <td><?= $registered ?></td>
                            <td><?= $present ?></td>
                            <td><?= $late ?></td>
                            <td><?= (int)$row['absent'] ?></td>
                            <td><?= $attendanceRate ?>%</td>
                        </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>

            </div>

            <!-- POINTS PER STUDENT -->
            <div class="form-card-container">

                <h2>Points Per Student Per Event</h2>

                <?php
                $avgPointsReport = $conn->query("
                    SELECT
                        u.name AS student_name,
                        COUNT(DISTINCT er.eventID) AS events_attended,
                        COALESCE(SUM(a.points),0) AS total_points,

                        ROUND(
                            COALESCE(SUM(a.points),0)
                            /
                            NULLIF(COUNT(DISTINCT er.eventID),0),
                            2
                        ) AS avg_points_per_event

                    FROM attendance a

                    JOIN event_registration er
                        ON a.registrationID = er.registrationID

                    JOIN user u
                        ON er.userID = u.userID

                    WHERE er.registrationStatus='Success'

                    GROUP BY er.userID

                    ORDER BY avg_points_per_event DESC
                ");
                ?>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Events Attended</th>
                        <th>Total Points</th>
                        <th>Average Points/Event</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php while($row = $avgPointsReport->fetch_assoc()): ?>

                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= (int)$row['events_attended'] ?></td>
                            <td><?= (int)$row['total_points'] ?></td>
                            <td><?= $row['avg_points_per_event'] ?></td>
                        </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>

            </div>

            <!-- STUDENT RANKINGS -->
            <div class="form-card-container">

                <h2>Top Student Rankings</h2>

                <?php
                $allStudents = $conn->query("
                    SELECT
                        s.userID,
                        u.name,
                        s.totalPoints,
                        s.recognitionLevel

                    FROM student s

                    JOIN user u
                        ON s.userID = u.userID

                    ORDER BY s.totalPoints DESC
                ");
                ?>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Rank</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Total Points</th>
                        <th>Recognition Level</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php
                    $rank = 1;
                    while($row = $allStudents->fetch_assoc()):
                    ?>

                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($row['userID']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= (int)$row['totalPoints'] ?></td>
                        <td><?= htmlspecialchars($row['recognitionLevel']) ?></td>
                    </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>

            </div>

        </div>

    </main>
</div>

<?php $conn->close(); ?>

</body>
</html>