<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - FK Club Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="justify-content: center; align-items: center; display: flex;">
    <div class="form-card-container" style="width: 400px;">
        <h2 style="text-align:center; margin-bottom:20px;">Login</h2>
        <?php
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                if ($user['role'] == 'Admin') header("Location: admin/dashboard.php");
                elseif ($user['role'] == 'Committee') header("Location: committee/dashboard.php");
                else header("Location: student/dashboard.php");
                exit();
            } else $error = "Invalid username or password.";
        }
        ?>
        <form method="post">
            <div class="form-group-row">
                <label>Username</label>
                <input type="text" name="username" class="input-control-text" required>
            </div>
            <div class="form-group-row">
                <label>Password</label>
                <input type="password" name="password" class="input-control-text" required>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <div class="form-actions-footer-bar">
                <button type="submit" class="btn btn-submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
