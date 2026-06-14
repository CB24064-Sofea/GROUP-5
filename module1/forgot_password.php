<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - FK Portal</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .reset-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            box-sizing: border-box;
            text-align: center;
        }
        .lock-icon-container {
            background-color: #f0f4f8;
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
        }
        .lock-icon-container svg {
            width: 24px;
            height: 24px;
            fill: #0a2540;
        }
        h2 {
            font-size: 20px;
            color: #0a2540;
            margin: 0 0 8px 0;
            font-weight: 600;
        }
        .subtitle {
            font-size: 13px;
            color: #627d98;
            margin: 0 0 28px 0;
            line-height: 1.5;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #0a2540;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
            width: 100%;
        }
        .input-control {
            width: 100%;
            height: 42px;
            padding: 10px 40px 10px 12px;
            font-size: 14px;
            border: 1px solid #d9e2ec;
            border-radius: 6px;
            background-color: #ffffff;
            box-sizing: border-box;
            color: #102a43;
        }
        .input-control:focus {
            outline: none;
            border-color: #627d98;
        }
        .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .toggle-btn svg {
            width: 20px;
            height: 20px;
            fill: #9fb3c8;
        }
        .validation-box {
            background-color: #f0f4f8;
            border-radius: 6px;
            padding: 16px;
            text-align: left;
            margin-bottom: 24px;
        }
        .criteria-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #627d98;
            margin-bottom: 8px;
        }
        .criteria-item:last-child {
            margin-bottom: 0;
        }
        .criteria-item svg {
            width: 16px;
            height: 16px;
            fill: #9fb3c8;
        }
        .criteria-item.valid {
            color: #0a2540;
        }
        .criteria-item.valid svg {
            fill: #22c55e;
        }
        .btn-reset {
            width: 100%;
            background-color: #0a2540;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            height: 44px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .btn-reset:hover {
            background-color: #193e68;
        }
        .back-to-login {
            display: inline-block;
            margin-top: 20px;
            color: #0284c7;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .back-to-login:hover {
            text-decoration: underline;
        }
        .alert-box {
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: left;
        }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    </style>
</head>
<body>

    <div class="reset-card">
        <div class="lock-icon-container">
            <svg viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
            </svg>
        </div>

        <h2>Set New Password</h2>
        <p class="subtitle">Please enter your new password below. Make sure it is at least 5 characters long.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert-box alert-danger"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['success'])): ?>
            <div class="alert-box alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <form action="process_reset.php" method="POST" id="resetForm">
            <div class="form-group">
                <label>User ID / Student ID</label>
                <div class="input-wrapper">
                    <input type="text" name="userID" class="input-control" placeholder="e.g. STU123456" required>
                </div>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <div class="input-wrapper">
                    <input type="password" id="new_password" name="new_password" class="input-control" placeholder="Enter 5+ characters" oninput="validatePassword()" required>
                    <button type="button" class="toggle-btn" onclick="toggleField('new_password', 'eye_icon_1')">
                        <svg id="eye_icon_1" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="input-control" placeholder="Re-enter your password" required>
                    <button type="button" class="toggle-btn" onclick="toggleField('confirm_password', 'eye_icon_2')">
                        <svg id="eye_icon_2" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    </button>
                </div>
            </div>

            <div class="validation-box">
                <div class="criteria-item" id="length-rule">
                    <svg viewBox="0 0 24 24" id="icon-length"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <span>At least 5 characters long</span>
                </div>
                <div class="criteria-item" id="special-rule">
                    <svg viewBox="0 0 24 24" id="icon-special"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <span>One special character or number</span>
                </div>
            </div>

            <button type="submit" class="btn-reset">Reset Password</button>
        </form>

        <a href="login.php" class="back-to-login">Back to Login</a>
    </div>

    <script>
        function toggleField(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (field.type === "password") {
                field.type = "text";
                icon.style.fill = "#0a2540";
            } else {
                field.type = "password";
                icon.style.fill = "#9fb3c8";
            }
        }

        function validatePassword() {
            const val = document.getElementById("new_password").value;
            const lengthRule = document.getElementById("length-rule");
            const specialRule = document.getElementById("special-rule");

            // Length verification rule checking matrix
            if (val.length >= 5) {
                lengthRule.classList.add("valid");
            } else {
                lengthRule.classList.remove("valid");
            }

            // Checks for a number or special symbol character matching criteria
            if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) {
                specialRule.classList.add("valid");
            } else {
                specialRule.classList.remove("valid");
            }
        }
    </script>
</body>
</html>