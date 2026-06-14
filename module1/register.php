<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Step up one directory level to access files in the root of GROUP 5
require_once '../config.php';

$message = '';
$error = '';
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = trim($_POST['userID']);
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $profilePhotoData = null;
    if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
        $profilePhotoData = file_get_contents($_FILES['profilePhoto']['tmp_name']);
    }

    if (empty($userID) || empty($username) || empty($name) || empty($email) || empty($password) || empty($phoneNumber) || $profilePhotoData === null || empty($role)) {
        $error = 'All fields are required, including the profile photo.';
    } else {
        // Verify if user ID already exists
        $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ? LIMIT 1");
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'User ID or Student ID already exists.';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Securely hash the password prior to saving into the database
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $conn->begin_transaction();
            
            try {
                $null = null;
                $stmt = $conn->prepare("INSERT INTO user (userID, username, email, password, phoneNumber, profilePhoto, role, name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssbsss", $userID, $username, $email, $hashedPassword, $phoneNumber, $null, $role, $name);
                $stmt->send_long_data(5, $profilePhotoData);
                $stmt->execute();
                $stmt->close();
                
                if ($role === 'Student') {
                    $stmtSub = $conn->prepare("INSERT INTO student (userID) VALUES (?)");
                    $stmtSub->bind_param("s", $userID);
                    $stmtSub->execute();
                    $stmtSub->close();
                } elseif ($role === 'Admin') {
                    $department = 'Student Affairs';
                    $createdAt = date('Y-m-d H:i:s');
                    $stmtSub = $conn->prepare("INSERT INTO admin (userID, department, createAt) VALUES (?, ?, ?)");
                    $stmtSub->bind_param("sss", $userID, $department, $createdAt);
                    $stmtSub->execute();
                    $stmtSub->close();
                }
                
                $conn->commit();
                $message = 'Registration successful! Redirecting to login page...';
                $redirect = true;
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FK Student Club & Event Management System</title>
    <?php if ($redirect): ?>
        <meta http-equiv="refresh" content="2;url=login.php">
    <?php endif; ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            box-sizing: border-box;
        }
        .main-content {
            width: 100%;
            max-width: 520px;
        }
        .form-card-container {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 40px 35px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .page-title {
            color: #0a2540;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .page-subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 32px 0;
        }
        .form-group-row {
            margin-bottom: 20px;
        }
        .form-group-row label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }
        .input-icon-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }
        .field-icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            fill: #94a3b8;
            pointer-events: none;
            z-index: 2;
        }
        .input-control-select {
            width: 100%;
            height: 46px;
            padding: 6px 42px 6px 42px;
            font-size: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            box-sizing: border-box;
            color: #0f172a;
            position: relative;
            z-index: 1;
        }
        select.input-control-select {
            padding-right: 14px;
        }
        .input-control-select:focus {
            outline: none;
            border-color: #0a2540;
        }
        .toggle-password-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
        }
        .toggle-password-btn svg {
            width: 18px;
            height: 18px;
            fill: #94a3b8;
        }
        .form-actions-footer-bar {
            margin-top: 32px;
        }
        .btn-submit {
            width: 100%;
            background-color: #0a2540;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            height: 46px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.15s ease;
        }
        .btn-submit:hover {
            background-color: #051424;
        }
        .registration-link-container {
            text-align: center;
            margin-top: 24px;
        }
        .btn-register {
            color: #0284c7;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-register:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .profile-upload-container {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .profile-preview {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }
        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-preview span {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
        }
        .upload-label {
            color: #0284c7;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        .upload-label:hover {
            text-decoration: underline;
        }
        .hidden-file-input {
            display: none;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-card-container">
            <h2 class="page-title">Create New Account</h2>
            <p class="page-subtitle">Join the academic portal to manage your enrollment and courses.</p>
            
            <?php if (!empty($message)): ?>
                <div class="alert" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fca5a5;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" enctype="multipart/form-data">
                <div class="form-group-row">
                    <label>Profile Photo</label>
                    <div class="profile-upload-container">
                        <div class="profile-preview" id="photo-preview">
                            <span>IMAGE/IMG</span>
                        </div>
                        <label for="profile-photo-input" class="upload-label">Upload Image</label>
                        <input type="file" name="profilePhoto" id="profile-photo-input" class="hidden-file-input" accept="image/*" required onchange="previewImage(this)">
                    </div>
                </div>

                <div class="form-group-row">
                    <label>User ID / Student ID</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.66 0 3 1.34 3 3s1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm6 12H6v-1c0-2 4-3.1 6-3.1s6 1.1 6 3.1v1z"/></svg>
                        <input type="text" name="userID" class="input-control-select" placeholder="e.g. STU123456" required>
                    </div>
                </div>

                <div class="form-group-row">
                    <label>Username</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
                        <input type="text" name="username" class="input-control-select" placeholder="@johndoe123" required>
                    </div>
                </div>
                
                <div class="form-group-row">
                    <label>Full Name</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5-4-8-4z"/></svg>
                        <input type="text" name="name" class="input-control-select" placeholder="John Doe" required>
                    </div>
                </div>
                
                <div class="form-group-row">
                    <label>Email Address</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        <input type="email" name="email" class="input-control-select" placeholder="email@university.edu" required>
                    </div>
                </div>

                <div class="form-group-row">
                    <label>Phone Number</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        <input type="text" name="phoneNumber" class="input-control-select" placeholder="+1 (555) 000-0000" required>
                    </div>
                </div>
                
                <div class="form-group-row">
                    <label>Password</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-1-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                        <input type="password" id="password-field" name="password" class="input-control-select" placeholder="••••••••" required>
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                            <svg id="eye-icon" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-group-row">
                    <label>Account Type</label>
                    <div class="input-icon-wrapper">
                        <svg class="field-icon" viewBox="0 0 24 24"><path d="M16.5 12c1.38 0 2.49-1.12 2.49-2.5S17.88 7 16.5 7C15.12 7 14 8.12 14 9.5s1.12 2.5 2.5 2.5zM9 11c1.66 0 2.99-1.34 2.99-3S10.66 5 9 5 6 6.34 6 8s1.34 3 3 3zm7.5 3c-1.83 0-5.5.92-5.5 2.75V19h11v-2.25c0-1.83-3.67-2.75-5.5-2.75zM9 13c-2.33 0-7 1.17-7 3.5V19h7v-2.25c0-.85.33-2.34 2.34-3.45-.87-.19-1.72-.3-2.34-.3z"/></svg>
                        <select name="role" class="input-control-select" required>
                            <option value="">Select Role</option>
                            <option value="Student">Student</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions-footer-bar">
                    <button class="btn btn-submit" type="submit">
                        <span>Register</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            </form>
            
            <div class="registration-link-container">
                <a href="login.php" class="btn btn-register">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password-field");
            const eyeIcon = document.getElementById("eye-icon");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = '<path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 8.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5zm10.79-4.21c-.49-1.05-1.18-1.99-2-2.82L19.33 10c.42.6.77 1.27 1.01 2-.73 2.16-2.52 3.95-4.68 4.68-.73-.24-1.4-.59-2-1.01l-1.54 1.54c.83.82 1.77 1.51 2.82 2 4.39-1.73 7.5-6 7.5-11 0-.42-.03-.84-.09-1.25zm-3.15-4.65l-1.41-1.41L17.5 4.5l-2.29 2.29C14.15 6.28 13.1 6.07 12 6.07c-5 0-9.27 3.11-11 7.5.49 1.05 1.18 1.99 2 2.82l1.46-1.46c-.42-.6-.77-1.27-1.01-2 .73-2.16 2.52-3.95 4.68-4.68.73.24 1.4.59 2 1.01l1.54-1.54c-.83-.82-1.77-1.51-2.82-2 1.06-.51 2.22-.84 3.44-.84s2.38.33 3.44.84L21.06 1.41 19.64 0l-1.42 1.42z"/>';
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('photo-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile Preview">';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '<span>IMAGE/IMG</span>';
            }
        }
    </script>
</body>
</html>