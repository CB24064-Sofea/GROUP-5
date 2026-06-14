<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['userID'])) {

    $sessionRole = strtolower(trim($_SESSION['role']));

    if ($sessionRole === 'admin' || $sessionRole === 'administrator') {
        header("Location: ../module4/admin/dashboard.php");
        exit();
    }

    if (
        $sessionRole === 'committee' ||
        $sessionRole === 'club committee member' ||
        $sessionRole === 'student' ||
        $sessionRole === 'undergraduate' ||
        $sessionRole === 'postgraduate'
    ) {
        header("Location: student/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WELCOME TO FK Student Club & Event Management System</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .main-content {
            width: 100%;
            max-width: 650px;
        }
        .workspace-stack {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .page-title {
            background-color: #ffffff;
            color: #243b53;
            text-align: center;
            margin: 0;
            padding: 18px;
            font-size: 22px;
            font-weight: bold;
            border: 1px solid #d9e2ec;
            border-radius: 4px;
        }
        .form-card-container {
            background-color: #ffffff;
            border: 1px solid #d9e2ec;
            border-radius: 4px;
            padding: 35px 45px;
        }
        .form-group-row {
            margin-bottom: 22px;
        }
        .form-group-row label {
            display: block;
            margin-bottom: 8px;
            color: #334e68;
            font-size: 14px;
            font-weight: 600;
        }
        .input-control-select {
            width: 100%;
            height: 40px;
            padding: 6px 12px;
            font-size: 14px;
            border: 1px solid #bcccdc;
            border-radius: 4px;
            background-color: #ffffff;
            box-sizing: border-box;
            color: #102a43;
        }
        .input-control-select:focus {
            outline: none;
            border-color: #627d98;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper input {
            padding-right: 40px;
        }
        .toggle-password-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-password-btn svg {
            width: 20px;
            height: 20px;
            fill: #627d98;
        }
        .form-actions-footer-bar {
            display: flex;
            justify-content: center;
            margin-top: 25px;
        }
        .btn-submit {
            background-color: #22c55e;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 10px 36px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .btn-submit:hover {
            background-color: #16a34a;
        }
        .registration-link-container {
            text-align: center;
            margin-top: 25px;
            border-top: 1px solid #f0f4f8;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-register, .btn-forgot {
            color: #627d98;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-register:hover, .btn-forgot:hover {
            text-decoration: underline;
            color: #334e68;
        }
        .error-message {
            background-color: #ffeef0;
            color: #d32f2f;
            border: 1px solid #f8d7da;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="workspace-stack">
            <h2 class="page-title">WELCOME TO FK Student Club & Event Management System</h2>
            <div class="form-card-container">
                
                <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
                    <div class="error-message" style="margin-bottom: 15px;">
                        Invalid Username, Password, or Role selection. Please try again.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
                    <div class="error-message" style="margin-bottom: 15px;">
                        Unauthorized Access. Please sign in with the proper role.
                    </div>
                <?php endif; ?>
                
                <form action="authenticate.php" method="POST">
                    
                    <div class="form-group-row">
                        <label>Username</label>
                        <input type="text" name="username" class="input-control-select" required>
                    </div>
                    
                    <div class="form-group-row">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password-field" name="password" class="input-control-select" required>
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                                <svg id="eye-icon" viewBox="0 0 24 24">
                                    <path id="eye-path" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group-row">
                        <label>Role</label>
                        <select name="role" class="input-control-select" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="student">Student</option>
                            <option value="committee">Committee</option>
                        </select>
                    </div>
                    
                    <div class="form-actions-footer-bar">
                        <button class="btn btn-submit" type="submit">Login</button>
                    </div>
                </form>
                
                <div class="registration-link-container">
                    <a href="forgot_password.php" class="btn btn-forgot">Forgot Password?</a>
                    <a href="register.php" class="btn btn-register">Register New User</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password-field");
            const eyePath = document.getElementById("eye-path");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyePath.setAttribute("d", "M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z");
            } else {
                passwordField.type = "password";
                eyePath.setAttribute("d", "M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 8.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z");
            }
        }
    </script>
</body>
</html>