<?php
require_once 'auth.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Participant List</title>
    <style>
        /* Your Standardized CSS */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f6fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .main-header { background-color: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; border-bottom: 2px solid #e0e0e0; height: 70px; }
        .app-container { display: flex; flex: 1; }
        .sidebar { width: 240px; background-color: #ffffff; border-right: 2px solid #e0e0e0; padding: 20px 15px; display: flex; flex-direction: column; }
        .nav-item { width: 100%; padding: 12px 15px; margin-bottom: 10px; border: 1px solid #dcdde1; border-radius: 5px; text-decoration: none; display: block; color: #333; font-weight: 500; }
        .nav-item:hover { background-color: #f1f2f6; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .btn-logout { margin-top: auto; padding: 12px; background-color: #feeaee; color: #c0392b; border: 1px solid #fab1a0; border-radius: 5px; text-align: center; text-decoration: none; font-weight: 600; }
        
        /* Specific Page Components */
        .page-title { text-align: center; font-size: 1.6rem; color: #2c3e50; border: 1px solid #b2bec3; background-color: #ffffff; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .form-card-container { background-color: #ffffff; border: 1px solid #b2bec3; border-radius: 6px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border: 1px solid #dcdde1; text-align: left; }
        th { background-color: #f8f9fa; }
        .btn-action { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; }
        .btn-view { background: #3498db; color: white; }
        .btn-remove { background: #e74c3c; color: white; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="header-left"><h1>FK Student Club & Event System</h1></div>
    <div class="header-right"><span class="admin-name"><?php echo $_SESSION['username']; ?></span></div>
</header>

<div class="app-container">
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="event_list.php" class="nav-item">Event Management</a>
            <a href="participants.php" class="nav-item" style="background-color: #f1f2f6;">Participant List</a>
        </nav>
        <a href="logout.php" class="btn-logout">Logout</a>
    </aside>

    <main class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">Event Participant List</h2>
            
            <div class="form-card-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Programme</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>FK20230123</td>
                            <td>Ayesha Khan</td>
                            <td>BS Computer Science</td>
                            <td>Confirmed</td>
                            <td>
                                <a href="#" class="btn-action btn-view">View</a>
                                <a href="#" class="btn-action btn-remove">Remove</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>