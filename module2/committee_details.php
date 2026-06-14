<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../config.php';


// Strict Role Guard - Restrict profile inspection exclusively to Admin accounts
if (!isAdmin()) {
   header("Location: /GROUP%205/module1/login.php");
    exit();


}

$committeeID = "";
$memberName  = "";
$position    = "";
$faculty     = "";
$clubID      = "";
$clubName    = "";
$errorMessage = "";

// Extract and parse primary tracking identifier parameter securely
if (isset($_GET['id'])) {
    $viewID = trim($_GET['id']);
    
    // Fetch profile variables securely via joint database parameterized search
    $sql = "
            SELECT
                cc.userID,
                cc.clubID,
                cc.position,
                u.name,
                c.clubName
            FROM club_committee cc
            INNER JOIN user u
                ON cc.userID = u.userID
            INNER JOIN club c
                ON cc.clubID = c.clubID
            WHERE cc.userID = ?
            LIMIT 1
        ";
            
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $viewID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $committeeID = $row['userID'];
            $memberName  = $row['name'];
            $position    = $row['position'];
            $clubID      = $row['clubID'];
            $clubName    = $row['clubName'];
            $faculty     = "-";
        } else {
            $errorMessage = "The requested committee executive profile could not be located in the ledger.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Database system execution failure.";
    }
} else {
    $errorMessage = "No committee executive tracking identifier was passed within the query dispatch.";
}

$page_title = (!empty($memberName)) ? htmlspecialchars($memberName) . " Profile Details" : "Committee Executive Profile Inspector";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="/GROUP%205/standard.css">
    <style>
        .wireframe-stack {
            width: 100%;
            max-width: 850px;
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin: 0 auto;
        }
        .section-block {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .section-title {
            font-size: 1.15rem;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0284c7;
        }
        .detail-row {
            display: flex;
            align-items: stretch;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
            border-radius: 6px;
            overflow: hidden;
        }
        .detail-label {
            width: 180px;
            background-color: #f8fafc;
            padding: 12px 16px;
            font-weight: 600;
            color: #475569;
            border-right: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
        }
        .detail-value {
            flex: 1;
            padding: 12px 16px;
            color: #0f172a;
            background-color: #ffffff;
        }
    </style>
</head>
<body style="background-color: #f8fafc;">

   <?php include __DIR__ . '/../topbar.php'; ?>


    <div class="app-container">
       <?php include __DIR__ . '/../sidebar.php'; ?>


        <main class="main-content">
            <div class="wireframe-stack">
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 10px;">
                    <div>
                        <h2 class="page-title" style="margin: 0;">Committee Profile Inspector</h2>
                        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Inspect systemic alignment profile parameters linked to leadership records.</p>
                    </div>
                    <a href="manage_committee.php" class="btn-secondary" style="text-decoration: none; padding: 10px 16px; font-size: 0.9rem; background-color: #e2e8f0; color: #334155; border-radius: 6px;">⬅️ Return to Roster</a>
                </div>
                
                <?php if (!empty($errorMessage)): ?>
                    <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 6px; border: 1px solid #fee2e2; font-weight: 500; text-align: center;">
                        ⚠️ <?= htmlspecialchars($errorMessage); ?>
                    </div>
                <?php else: ?>
                    
                    <section class="section-block">
                        <h3 class="section-title">Executive Identity Ledger</h3>
                        
                        <div class="detail-row">
                            <div class="detail-label">Committee ID</div>
                            <div class="detail-value"><code><?= htmlspecialchars($committeeID); ?></code></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value" style="font-weight: 600; color: #0f172a;">👤 <?= htmlspecialchars($memberName); ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Assigned Office</div>
                            <div class="detail-value"><span style="background-color: #f0fdf4; color: #166534; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($position); ?></span></div>
                        </div>

                        
                        <div class="detail-row">
                            <div class="detail-label">Affiliated Club</div>
                            <div class="detail-value" style="font-weight: 500; color: #0284c7;">
                                🏢 <?= htmlspecialchars($clubName); ?> 
                                <?php if (!empty($clubID)): ?>
                                    <span style="font-size: 0.85rem; color: #64748b;">(ID: <code><?= htmlspecialchars($clubID); ?></code>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <div class="action-container" style="display: flex; justify-content: flex-end; gap: 12px;">
                       <a href="edit_committee.php?uid=<?= urlencode($committeeID) ?>&cid=<?= urlencode($clubID) ?>"> class="btn-primary" style="text-decoration: none; padding: 10px 20px; color: white; background-color: #0284c7; border-radius: 6px; font-weight: bold; font-size: 0.9rem;">📝 Edit Profile</a>
                        <a href="manage_committee.php" class="btn-secondary" style="text-decoration: none; padding: 10px 20px; background-color: #cbd5e1; color: #334155; border-radius: 6px; font-size: 0.9rem;">Back to List</a>
                    </div>

                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>