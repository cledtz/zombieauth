<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>
</head>

<?php
include "conn/conn.php";

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['invite'])) {

    $stmt = $conn->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt = $conn->prepare("SELECT * FROM `invites` WHERE `invite` = ?");
    $stmt->bind_param("s", $_POST['invite']);
    $stmt->execute();
    $invquery = $stmt->get_result();
    $invdata = $invquery->fetch_assoc();

    if ($result->num_rows > 0) {
        echo '<div class="alert alert-danger" role="alert">Username is taken.</div>';
        return;
    }

    if ($invquery->num_rows == 0) {
        echo '<div class="alert alert-danger" role="alert">Invite doesn\'t exist.</div>';
        return;
    }

    if ($invdata['used'] == 1) {
        echo '<div class="alert alert-danger" role="alert">Invite has been redeemed already.</div>';
        return;
    }

    if ($invdata['ban_reason'] != NULL) {
        echo '<div class="alert alert-danger" role="alert">Invite is banned (Reason: ' . $invdata['ban_reason'] . ')</div>';
        return;
    }

    $stmt = $conn->prepare("UPDATE `invites` SET `used` = 1 WHERE `invite` = ?");
    $stmt->bind_param("s", $_POST['invite']);
    $stmt->execute();

    $hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO `accounts`(`username`,`password`,`role`) VALUES(?, ?, ?)");
    $stmt->bind_param("sss", $_POST['username'], $hashed_password, $invdata['role']);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE `invites` SET `used_by` = ? WHERE `invite` = ?");
    $stmt->bind_param("ss", $_POST['username'], $_POST['invite']);
    $stmt->execute();


    session_start();
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['role'] = $invdata['role'];
    echo '<div class="alert alert-success" role="alert">Registration has been successful.</div>';
    header("Refresh:2; url=dashboard.php");
}
?>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mb-auto p-5">
                <div class="card">
                    <div class="card-header">Register</div>

                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group row">
                                <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

                                <div class="col-md-6">
                                    <input id="username" type="text" class="form-control" name="username" value="" required="" autofocus="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="invite" class="col-md-4 col-form-label text-md-right">Invite</label>

                                <div class="col-md-6">
                                    <input id="invite" type="text" class="form-control" name="invite" required="">
                                </div>
                            </div>
                            <div class="form-group d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Register
                                </button>
                                <div class="ml-2">
                                    <button class="btn btn-primary btn-lg" onclick="window.location.href='\\panel\\index.php'">
                                        Go back
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