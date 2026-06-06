<?php if (!isset($skipAuth)) require_once 'auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club & Event Management</title>
    <style>
        /* Paste your full CSS here (the one you provided) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f6fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .main-header { background-color: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; border-bottom: 2px solid #e0e0e0; height: 70px; }
        .header-left { display: flex; align-items: center; gap: 20px; }
        .logo-placeholder img { height: 45px; width: auto; object-fit: contain; }
        .header-left h1 { font-size: 1.4rem; color: #2c3e50; }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .admin-name { font-weight: 600; color: #555; }
        .profile-container { width: 42px; height: 42px; border-radius: 50%; overflow: hidden; }
        .profile-container img { width: 100%; height: 100%; object-fit: cover; }
        .profile-fallback { font-size: 0.9rem; font-weight: 700; color: #3498db; text-transform: uppercase; }
        .app-container { display: flex; flex: 1; }
        .sidebar { width: 240px; background-color: #ffffff; border-right: 2px solid #e0e0e0; display: flex; flex-direction: column; justify-content: space-between; padding: 20px 15px; }
        .sidebar-nav { display: flex; flex-direction: column; gap: 10px; }
        .nav-item, .sub-nav-item, .btn-logout { width: 100%; padding: 12px 15px; background: none; border: 1px solid #dcdde1; border-radius: 5px; text-align: left; font-size: 0.95rem; cursor: pointer; font-weight: 500; transition: all 0.2s ease; text-decoration: none; display: block; color: #333333; }
        .nav-item:hover, .btn-logout:hover { background-color: #f1f2f6; border-color: #b2bec3; }
        .submenu-container { border: 1px solid #b2bec3; border-radius: 5px; background-color: #fafafa; overflow: hidden; }
        .submenu-container .nav-item { border: none; border-bottom: 1px solid #dcdde1; border-radius: 0; background-color: #f1f2f6; font-weight: bold; }
        .submenu { display: flex; flex-direction: column; }
        .sub-nav-item { border: none; border-bottom: 1px solid #eee; border-radius: 0; padding-left: 30px; font-size: 0.9rem; text-decoration: none; color: #333; }
        .sub-nav-item:last-child { border-bottom: none; }
        .active-sub { background-color: #3498db !important; color: white; }
        .btn-logout { margin-top: auto; background-color: #feeaee; color: #c0392b; border-color: #fab1a0; text-align: center; font-weight: 600; text-decoration: none; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .workspace-stack { width: 100%; max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }
        .page-title { text-align: center; font-size: 1.6rem; color: #2c3e50; border: 1px solid #b2bec3; background-color: #ffffff; padding: 12px; border-radius: 5px; }
        .form-card-container { background-color: #ffffff; border: 1px solid #b2bec3; border-radius: 6px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .form-group-row { display: flex; flex-direction: column; gap: 8px; margin-bottom: 22px; }
        .form-group-row label { font-weight: 600; color: #2c3e50; font-size: 0.95rem; }
        .input-control-select, .input-control-date, input, textarea, select { width: 100%; padding: 12px 14px; font-size: 0.95rem; border: 1px solid #b2bec3; border-radius: 5px; background-color: #ffffff; outline: none; color: #333; transition: border-color 0.2s; }
        .form-actions-footer-bar { display: flex; justify-content: center; gap: 15px; margin-top: 10px; }
        .btn { padding: 12px 24px; font-size: 0.95rem; font-weight: 600; border-radius: 5px; cursor: pointer; border: none; text-decoration: none; display: inline-block; text-align: center; transition: all 0.2s; min-width: 160px; }
        .btn-submit { background-color: #2ecc71; color: white; border: 1px solid #27ae60; }
        .btn-submit:hover { background-color: #27ae60; }
        .btn-cancel { background-color: #ffffff; color: #333; border: 1px solid #b2bec3; }
        .btn-cancel:hover { background-color: #f1f2f6; }
        .alert { padding: 12px 18px; border-radius: 5px; font-weight: 600; font-size: 0.95rem; text-align: center; border: 1px solid transparent; margin-bottom: 5px; }
        .alert-error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="main-header">
    <div class="header-left">
        <div class="logo-placeholder"><img src="https://via.placeholder.com/45?text=UMPSA" alt="Logo"></div>
        <h1>FK Student Club & Event Management</h1>
    </div>
    <div class="header-right">
        <span class="admin-name"><?php echo htmlspecialchars(getUserName()); ?> (<?php echo getUserRole(); ?>)</span>
        <div class="profile-container"><div class="profile-fallback">👤</div></div>
    </div>
</div>
<div class="app-container">