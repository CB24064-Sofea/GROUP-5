<?php
session_start();

if (isset($_SESSION['userID'])) {

    if ($_SESSION['role'] == "Admin") {
        header("Location: admin/dashboard.php");
    }

    if ($_SESSION['role'] == "Student") {
        header("Location: student/dashboard.php");
    }

    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Club Management System</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

    <div class="main-content">

        <div class="workspace-stack">

            <h2 class="page-title">
                Club Management System Login
            </h2>

            <div class="form-card-container">

                <form action="authenticate.php"
                    method="POST">

                    <div class="form-group-row">

                        <label>Username</label>

                        <input
                            type="text"
                            name="username"
                            class="input-control-select"
                            required>

                    </div>

                    <div class="form-group-row">

                        <label>Password</label>

                        <input
                            type="password"
                            name="password"
                            class="input-control-select"
                            required>

                    </div>

                    <div class="form-group-row">

                        <label>Role</label>

                        <select
                            name="role"
                            class="input-control-select"
                            required>

                            <option value="">
                                Select Role
                            </option>

                            <option value="Admin">
                                Admin
                            </option>

                            <option value="Student">
                                Student
                            </option>

                        </select>

                    </div>

                    <div class="form-actions-footer-bar">

                        <button
                            class="btn btn-submit"
                            type="submit">

                            Login

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>