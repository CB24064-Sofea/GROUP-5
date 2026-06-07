<?php

include("../includes/auth.php");
include("../config/database.php");

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    $sql = "
    INSERT INTO user
    (name,username,email,password,phoneNumber,role)
    VALUES
    (?,?,?,?,?,?)
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $name,
        $username,
        $email,
        $password,
        $phone,
        $role
    );

    mysqli_stmt_execute($stmt);

    header("Location: users.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add User</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body>

    <div class="main-content">

        <div class="workspace-stack">

            <div class="page-title">
                Add New User
            </div>

            <div class="form-card-container">

                <form method="POST">

                    <div class="form-group-row">

                        <label>Name</label>

                        <input
                            type="text"
                            name="name"
                            class="input-control-select"
                            required>

                    </div>

                    <div class="form-group-row">

                        <label>Username</label>

                        <input
                            type="text"
                            name="username"
                            class="input-control-select"
                            required>

                    </div>

                    <div class="form-group-row">

                        <label>Email</label>

                        <input
                            type="email"
                            name="email"
                            class="input-control-select"
                            required>

                    </div>

                    <div class="form-group-row">

                        <label>Password</label>

                        <input
                            type="text"
                            name="password"
                            class="input-control-select"
                            required>

                    </div>

                    <div class="form-group-row">

                        <label>Phone Number</label>

                        <input
                            type="text"
                            name="phone"
                            class="input-control-select">

                    </div>

                    <div class="form-group-row">

                        <label>Role</label>

                        <select
                            name="role"
                            class="input-control-select">

                            <option value="Student">Student</option>
                            <option value="Admin">Admin</option>
                            <option value="Committee">Committee</option>

                        </select>

                    </div>

                    <div class="form-actions-footer-bar">

                        <button
                            type="submit"
                            name="submit"
                            class="btn btn-submit">

                            Save User

                        </button>

                        <a href="../admin/"
                            class="btn btn-cancel">

                            Back

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>