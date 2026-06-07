<?php

include("../includes/auth.php");
include("../config/database.php");

$id = $_GET['id'];

$result = mysqli_query(
    $conn,
    "SELECT * FROM user WHERE userID='$id'"
);

$user = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {

    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    mysqli_query(
        $conn,
        "UPDATE user
        SET
        name='$name',
        username='$username',
        email='$email',
        phoneNumber='$phone',
        role='$role'
        WHERE userID='$id'"
    );

    header("Location: users.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit User</title>

    <link rel="stylesheet"
        href="../assets/style.css">

</head>

<body>

    <div class="main-content">

        <div class="workspace-stack">

            <div class="page-title">

                Edit User

            </div>

            <div class="form-card-container">

                <form method="POST">

                    <div class="form-group-row">

                        <label>Name</label>

                        <input
                            type="text"
                            name="name"
                            value="<?= $user['name']; ?>"
                            class="input-control-select">

                    </div>

                    <div class="form-group-row">

                        <label>Username</label>

                        <input
                            type="text"
                            name="username"
                            value="<?= $user['username']; ?>"
                            class="input-control-select">

                    </div>

                    <div class="form-group-row">

                        <label>Email</label>

                        <input
                            type="email"
                            name="email"
                            value="<?= $user['email']; ?>"
                            class="input-control-select">

                    </div>

                    <div class="form-group-row">

                        <label>Phone</label>

                        <input
                            type="text"
                            name="phone"
                            value="<?= $user['phoneNumber']; ?>"
                            class="input-control-select">

                    </div>

                    <div class="form-group-row">

                        <label>Role</label>

                        <select
                            name="role"
                            class="input-control-select">

                            <option value="Student"
                                <?= $user['role'] == "Student" ? "selected" : "" ?>>

                                Student

                            </option>

                            <option value="Admin"
                                <?= $user['role'] == "Admin" ? "selected" : "" ?>>

                                Admin

                            </option>

                            <option value="Committee"
                                <?= $user['role'] == "Committee" ? "selected" : "" ?>>

                                Committee

                            </option>

                        </select>

                    </div>

                    <div class="form-actions-footer-bar">

                        <button
                            type="submit"
                            name="update"
                            class="btn btn-submit">

                            Update User

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>