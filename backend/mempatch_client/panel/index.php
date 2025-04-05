<?php
include "conn/conn.php";

if (isset($_POST['username']) && isset($_POST['password'])) {
    session_start();

    $stmt = $conn->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($result->num_rows < 1) {
        echo '<div class="alert alert-danger" role="alert">Account does not exist.</div>';
    }
    else if ($row['ban_reason'] != NULL) {
        echo '<div class="alert alert-danger" role="alert">Account is banned. Reason: ' . $row['ban_reason'] . '</div>';
    }
    else if (!password_verify($_POST['password'], $row["password"])) {
        echo '<div class="alert alert-danger" role="alert">Incorrect password.</div>';
    }
    else if (password_verify($_POST['password'], $row["password"])) {
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['role'] = $row['role'];
        echo '<div class="alert alert-info" role="alert">Success.</div>';
        header("Refresh:1; url=dashboard.php");
    }
}
?>

<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mb-auto p-5">
                <div class="card">
                    <div class="card-header">Login</div>

                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group row">
                                <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

                                <div class="col-md-6">
                                    <input id="username" type="text" class="form-control" name="username" value=""
                                        required="" autofocus="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password"
                                        required="">
                                </div>
                            </div>

                            <div class="form-group d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Login
                                </button>
                                <div class="ml-2">
                                <button class="btn btn-primary btn-lg" onclick="window.location.href='/panel/register.php'">
                                    Register
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>