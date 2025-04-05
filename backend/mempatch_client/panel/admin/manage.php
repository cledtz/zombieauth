<?php

include '../conn/conn.php';



session_start();



if (!isset($_SESSION['username'])) {

    header("Location: /");

    exit();
}

$stmt = $conn->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['ban_reason'] != NULL)
{
    header("Location: logout.php");
}

if ($row['role'] != 'administrator' && $row['role'] != 'management') {

    $_SESSION['errormessage'] = "You are not allowed to be on this site.";

    header("Location: ../dashboard.php");

    exit();
}

if (isset($_POST['username']))
{
    if (isset($_POST['reason'])) 
    {
        if (isset($_POST['banuser'])) 
        {

            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_POST['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == 0) {

                echo '<div class="alert alert-danger" role="alert">Incorrect username.</div>';
            }

            $HWID = $row['hwid'];



            $username = $_POST['username'];

            $reason = $_POST['reason'];



            $stmt = $conn->prepare("UPDATE `user` SET `ban_reason` = ? WHERE `username` = ?");
            $stmt->bind_param("ss", $reason, $username);
            $stmt->execute();


            if (isset($_POST['banhwid']) == 1) {

                $stmt2 = $conn->prepare("SELECT * FROM `blacklist` WHERE `hwid` = ?");
                $stmt2->bind_param("s", $HWID);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $row2 = $result2->fetch_assoc();

                if ($result2->num_rows != 0) {

                    echo '<div class="alert alert-info" role="alert">Banned account, the HWID is already banned from the client.</div>';
                } else {

                    $stmt = $conn->prepare("INSERT INTO `blacklist` (`hwid`, `reason`) VALUES (?, ?)");
                    $stmt->bind_param("ss", $HWID, $reason);
                    $stmt->execute();

                    echo '<div class="alert alert-info" role="alert">Banned account, banned HWID of client.</div>';
                }
            } else {
                echo '<div class="alert alert-info" role="alert">Banned account.</div>';
            }
        } else if (isset($_POST['unbanuser'])) 
        {

            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_POST['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();


            if ($result->num_rows < 1) {

                echo '<div class="alert alert-danger" role="alert">Incorrect username.</div>';
            }



            $username = $_POST['username'];



            $NULL = NULL;
            $stmt = $conn->prepare("UPDATE `user` SET `ban_reason` = ? WHERE `username` = ?");
            $stmt->bind_param("ss", $NULL, $username);
            $stmt->execute();

            $stmt2 = $conn->prepare("SELECT * FROM `blacklist` WHERE `hwid` = ?");
            $stmt2->bind_param("s", $row['hwid']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();

            if ($result2->num_rows > 0) {

                $stmt = $conn->prepare("DELETE FROM `blacklist` WHERE `hwid` = ?");
                $stmt->bind_param("s", $row['hwid']);
                $stmt->execute();

                echo '<div class="alert alert-info" role="alert">Removed ban of account, removed client HWID ban.</div>';
            } else {
                echo '<div class="alert alert-info" role="alert">Removed ban of account.</div>';
            }
        }
    }


    if (isset($_POST['reset'])) {
        $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
        $stmt->bind_param("s", $_POST['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($result->num_rows < 1) {

            echo '<div class="alert alert-danger" role="alert">Incorrect username.</div>';
        }
        else
        {
            $username = $_POST['username'];

            $NULL = NULL;
            $stmta = $conn->prepare("UPDATE `user` SET `hwid` = ? WHERE `username` = ?");
            $stmta->bind_param("ss", $NULL, $username);
            $stmta->execute();
    
    
            echo '<div class="alert alert-info" role="alert">HWID reset.</div>';
        }
    }

    if (isset($_POST['reset_password'])) {
        $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
        $stmt->bind_param("s", $_POST['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($result->num_rows < 1) {

            echo '<div class="alert alert-danger" role="alert">Incorrect username.</div>';
        }
        else
        {
            $username = $_POST['username'];

            $NULL = NULL;
            $stmta = $conn->prepare("UPDATE `user` SET `password` = ? WHERE `username` = ?");
            $stmta->bind_param("ss", $NULL, $username);
            $stmta->execute();
    
    
            echo '<div class="alert alert-info" role="alert">Password reset.</div>';
        }
    }


    // if (isset($_POST['applycompensation']) && isset($_POST['hours'])) {

    //     $stmt = $conn->prepare("SELECT * FROM `user` WHERE `license` = ?");
    //     $stmt->bind_param("s", $_POST['license']);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $row = $result->fetch_assoc();


    //     if ($result->num_rows < 1) {

    //         echo '<div class="alert alert-danger" role="alert">Incorrect license.</div>';
    //     }

    //     $current_time = date('d-m-Y H:i:s');
    //     $current_now_time = new DateTime($current_time);
    //     $server_expires_at_time = new DateTime($row['expires_at']);

    //     if ($current_now_time <= $server_expires_at_time && $row['redeemed_at'] != null) {
    //         $duration = $_POST['hours'];
    //         $duration2 = $duration . ' hours';
    //         $expiration = date('d-m-Y H:i:s', strtotime($row['expires_at'] . ' + ' . $duration2));

    //         $license = $_POST['license'];
    //         $stmta = $conn->prepare("UPDATE `licenses` SET `expires_at` = ? WHERE `license` = ?");
    //         $stmta->bind_param("ss", $expiration, $license);
    //         $stmta->execute();

    //         echo '<div class="alert alert-info" role="alert">Added ' . $_POST['hours'] . ' hour(s).</div>';
    //     } else {
    //         echo '<div class="alert alert-danger" role="alert">You can not compensate expired or non active licenses.</div>';
    //     }
    // }
}

if (isset($_POST['license'])) {

    if (isset($_POST['reason'])) 
    {
        if (isset($_POST['ban'])) 
        {

            $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
            $stmt->bind_param("s", $_POST['license']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();


            if ($result->num_rows < 1) {

                echo '<div class="alert alert-danger" role="alert">Incorrect license.</div>';
            }



            $license = $_POST['license'];

            $reason = $_POST['reason'];



            $stmt = $conn->prepare("UPDATE `licenses` SET `ban_reason` = ? WHERE `license` = ?");
            $stmt->bind_param("ss", $reason, $license);
            $stmt->execute();
        } else if (isset($_POST['unban'])) 
        {

            $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
            $stmt->bind_param("s", $_POST['license']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();


            if ($result->num_rows < 1) {

                echo '<div class="alert alert-danger" role="alert">Incorrect license.</div>';
            }

            $license = $_POST['license'];

            $NULL = NULL;
            $stmt = $conn->prepare("UPDATE `licenses` SET `ban_reason` = ? WHERE `license` = ?");
            $stmt->bind_param("ss", $NULL, $license);
            $stmt->execute();

            echo '<div class="alert alert-info" role="alert">Removed ban of license.</div>';
            
        }
    }

    if (isset($_POST['reset2'])) 
    {
        $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
        $stmt->bind_param("s", $_POST['license']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($result->num_rows < 1) {

            echo '<div class="alert alert-danger" role="alert">Incorrect license.</div>';
        }
        else
        {
            if ($row['user'] == NULL)
            {
                echo '<div class="alert alert-danger" role="alert">License has not been redeemed yet.</div>';
            }
            else
            {
                $username = $row['user'];

                $NULL = NULL;
                $stmta = $conn->prepare("UPDATE `user` SET `hwid` = ? WHERE `username` = ?");
                $stmta->bind_param("ss", $NULL, $username);
                $stmta->execute();
        
        
                echo '<div class="alert alert-info" role="alert">HWID reset.</div>';
            }
        }
    }

    if (isset($_POST['applysubscription']) && isset($_POST['subscription'])) {

        $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
        $stmt->bind_param("s", $_POST['license']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();




        if ($result->num_rows < 1) {

            echo '<div class="alert alert-danger" role="alert">Incorrect license.</div>';
        }

        $license = $_POST['license'];
        $stmta = $conn->prepare("UPDATE `licenses` SET `product` = ? WHERE `license` = ?");
        $stmta->bind_param("ss", $_POST['subscription'], $license);
        $stmta->execute();


        echo '<div class="alert alert-info" role="alert">Changed subscription to: ' . $_POST['subscription'] . '</div>';
    }
}


if (isset($_POST['compensateall']) && isset($_POST['hours']) && isset($_POST['subscription'])) 
{

    $result = $conn->query("SELECT * FROM `subscriptions`");
    $totalCompensated = 0;
    while ($row = $result->fetch_assoc()) 
    {
        if ($row['subscription'] == $_POST['subscription'])
        {
            $current_time = date('d-m-Y H:i:s');
            $current_now_time = new DateTime($current_time);
            $server_expires_at_time = new DateTime($row['expires_at']);
    
            if ($current_now_time <= $server_expires_at_time) 
            {
                $duration = $_POST['hours'];
                $duration2 = $duration . ' hours';
                $expiration = date('d-m-Y H:i:s', strtotime($row['expires_at'] . ' + ' . $duration2));
    
                $user = $row['user'];
                $stmta = $conn->prepare("UPDATE `subscriptions` SET `expires_at` = ? WHERE `user` = ? AND `subscription` = ?");
                $stmta->bind_param("sss", $expiration, $user, $_POST['subscription']);
                $stmta->execute();

                $totalCompensated++;
           }
         }
     }

     if ($totalCompensated != NULL)
        echo '<div class="alert alert-info" role="alert">Added ' . $_POST['hours'] . ' hour(s) to ' . $totalCompensated . ' accounts(s) with an active ' . $_POST['subscription'] . ' subscription.</div>';
    else
        echo '<div class="alert alert-info" role="alert">No active subscription(s) found!</div>';
}

?>


<html>



<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Manage</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>

    <style>
        .unselectable {

            -webkit-touch-callout: none;

            -webkit-user-select: none;

            -khtml-user-select: none;

            -moz-user-select: none;

            -ms-user-select: none;

            user-select: none;

        }
    </style>

</head>



<body>



    <nav class="navbar navbar-expand-lg navbar navbar-light" style="background-color: #ececec;">

        <div class="container">

            <a class="navbar-brand" href="/">



            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>

            </button>



            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                <ul class="navbar-nav mr-auto">

                    <li class="nav-item dropdown">

                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                            Main

                        </a>

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                            <a class="dropdown-item" href="\panel\dashboard.php">Licenses</a>

                            <a class="dropdown-item" href="\panel\manage.php">Manage</a>

                        </div>

                    </li>

                    <?php

                    if (isset($_SESSION['username'])) {
                        $stmt = $conn->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
                        $stmt->bind_param("s", $_SESSION['username']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();

                        if ($row['role'] == "administrator" || $row['role'] == "management")  {

                    ?>
                            <li class="nav-item dropdown">

                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                    License administration

                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                                    <a class="dropdown-item" href="\panel\admin\licenses.php">Licenses</a>

                                    <a class="dropdown-item" href="\panel\admin\generate.php">Generate</a>

                                    <a class="dropdown-item" href="\panel\admin\manage.php">Manage</a>
                                    
                                    <a class="dropdown-item" href="\panel\admin\accounts.php">Accounts</a>


                                </div>

                            </li>

                    <?php
                        }
                    }
                    ?>
                    <?php

                    if (isset($_SESSION['username'])) {
                        $stmt = $conn->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
                        $stmt->bind_param("s", $_SESSION['username']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();

                        if ($row['role'] == "administrator") {

                    ?>
                            <li class="nav-item dropdown">

                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                    Panel administration

                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                                    <a class="dropdown-item" href="\panel\admin\generateinvites.php">Generate invites</a>

                                    <a class="dropdown-item" href="\panel\admin\invites.php">All invites</a>

                                    <a class="dropdown-item" href="\panel\admin\manageinvite.php">Manage invite</a>

                                    <a class="dropdown-item" href="\panel\admin\panelaccounts.php">All panel accounts</a>

                                    <a class="dropdown-item" href="\panel\admin\managepanelaccounts.php">Manage panel account</a>

                                    <a class="dropdown-item" href="\panel\admin\products.php">All products</a>

                                    <a class="dropdown-item" href="\panel\admin\manageproducts.php">Manage products</a>


                                </div>

                            </li>

                    <?php
                        }
                    }
                    ?>


                </ul>

                <div class="d-flex flex-row justify-content-between">

                    <form class="form-inline my-lg-0" action="\panel\info.php" method="GET">

                        <input class="form-control mr-sm-2" type="text" placeholder="Go to license ..." name="url" aria-label="Search" style="width: 500px;">

                        <button class="btn btn-outline-success" type="submit">Go</button>

                    </form>

                    <ul class="navbar-nav">

                        <li class="nav-item active ml-sm-2">

                            <a class="btn btn-primary" href="\panel\logout.php" role="button">Logout</a>

                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </nav>


    <div class="container">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-8 mb-auto p-5">

            <div class="card">

                <div class="card-header">Manage user account status</div>

                <div class="card-body">

                    <form method="POST">

                        <div class="form-group row">

                            <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

                            <div class="col-md-6">

                                <input id="username" type="text" class="form-control" name="username" required="" placeholder="Username">

                            </div>

                        </div>



                        <div class="form-group row">

                            <label for="reason" class="col-md-4 col-form-label text-md-right">Reason</label>

                            <div class="col-md-6">

                                <input id="reason" value="" type="text" class="form-control" name="reason" required="" placeholder="Reason">

                            </div>

                        </div>



                        <div class="mt-4 form-group d-flex justify-content-center">

                            <button id="banuser" name="banuser" type="submit" class="btn btn-primary btn-md">

                                Ban

                            </button>

                            <div class="ml-2"></div>

                            <button id="unbanuser" name="unbanuser" type="submit" class="btn btn-primary btn-md">

                                Remove ban

                            </button>

                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="banhwid" name="banhwid">
                            <label class="form-check-label" for="banhwid">
                                Ban hardware-ID
                            </label>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<div class="container">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-8 mb-auto p-5">

                <div class="card">

                    <div class="card-header">Reset HWID of account</div>



                    <div class="card-body">

                        <form method="POST">

                            <div class="form-group row">

                                <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

                                <div class="col-md-6">

                                    <input id="username" type="text" class="form-control" name="username" required="" placeholder="Username">

                                </div>

                            </div>


                            <div class="mt-4 form-group d-flex justify-content-center">

                                <button id="reset" name="reset" type="submit" class="btn btn-primary btn-md">

                                    Reset

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="container">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-8 mb-auto p-5">

                <div class="card">

                    <div class="card-header">Reset HWID of account by license</div>



                    <div class="card-body">

                        <form method="POST">

                            <div class="form-group row">

                                <label for="license" class="col-md-4 col-form-label text-md-right">License</label>

                                <div class="col-md-6">

                                    <input id="license" type="text" class="form-control" name="license" required="" placeholder="License">

                                </div>

                            </div>


                            <div class="mt-4 form-group d-flex justify-content-center">

                                <button id="reset2" name="reset2" type="submit" class="btn btn-primary btn-md">

                                    Reset

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="container">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-md-8 mb-auto p-5">

                    <div class="card">

                        <div class="card-header">Manage license status</div>



                        <div class="card-body">

                            <form method="POST">

                                <div class="form-group row">

                                    <label for="license" class="col-md-4 col-form-label text-md-right">License</label>

                                    <div class="col-md-6">

                                        <input id="license" type="text" class="form-control" name="license" required="" placeholder="License">

                                    </div>

                                </div>



                                <div class="form-group row">

                                    <label for="reason" class="col-md-4 col-form-label text-md-right">Reason</label>

                                    <div class="col-md-6">

                                        <input id="reason" value="" type="text" class="form-control" name="reason" required="" placeholder="Reason">

                                    </div>

                                </div>



                                <div class="mt-4 form-group d-flex justify-content-center">

                                    <button id="ban" name="ban" type="submit" class="btn btn-primary btn-md">

                                        Ban

                                    </button>

                                    <div class="ml-2"></div>

                                    <button id="unban" name="unban" type="submit" class="btn btn-primary btn-md">

                                        Remove ban

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>



</body>

    <div class="container">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-md-8 mb-auto p-5">

                    <div class="card">

                        <div class="card-header">Change subscription of license</div>



                        <div class="card-body">

                            <form method="POST">

                                <div class="form-group row">

                                    <label for="license" class="col-md-4 col-form-label text-md-right">License</label>

                                    <div class="col-md-6">

                                        <input id="license" type="text" class="form-control" name="license" required="" placeholder="License">

                                    </div>

                                </div>
                                <div class="form-group row">

                                    <label for="subscription" class="col-md-4 col-form-label text-md-right">Subscription</label>

                                    <div class="col-md-6">

                                        <select class="form-control" name="subscription" id="subscription">

                                            <?php

                                            $result = $conn->query("SELECT * FROM `products`");

                                            while ($row = $result->fetch_assoc()) {

                                                echo "<option value=\"{$row['product']}\">{$row['product']}</option>";
                                            }

                                            ?>

                                        </select>

                                    </div>

                                </div>

                                <div class="mt-4 form-group d-flex justify-content-center">

                                    <button id="applysubscription" name="applysubscription" type="submit" class="btn btn-primary btn-md">

                                        Save changes

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="container">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-8 mb-auto p-5">

            <div class="card">

                <div class="card-header">Reset password of account</div>



                <div class="card-body">

                    <form method="POST">

                        <div class="form-group row">

                            <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

                            <div class="col-md-6">

                                <input id="username" type="text" class="form-control" name="username" required="" placeholder="Username">

                            </div>

                        </div>


                        <div class="mt-4 form-group d-flex justify-content-center">

                            <button id="reset_password" name="reset_password" type="submit" class="btn btn-primary btn-md">

                                Reset

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

        <!-- <div class="container">

            <div class="row justify-content-center">

                <div class="col-md-8 mb-auto p-5">

                    <div class="card">

                        <div class="card-header">Compensate time of account subscription</div>



                        <div class="card-body">

                            <form method="POST">

                                <div class="form-group row">

                                    <label for="license" class="col-md-4 col-form-label text-md-right">License</label>

                                    <div class="col-md-6">

                                        <input id="license" type="text" class="form-control" name="license" required="" placeholder="License">

                                    </div>

                                </div>
                                <div class="form-group row">

                                    <label for="hours" class="col-md-4 col-form-label text-md-right">Hours</label>

                                    <div class="col-md-6">

                                        <input id="hours" type="text" class="form-control" name="hours" required="" placeholder="Amount of hours.">

                                    </div>

                                </div>

                                <div class="mt-4 form-group d-flex justify-content-center">

                                    <button id="applycompensation" name="applycompensation" type="submit" class="btn btn-primary btn-md">

                                        Compensate

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>
         -->

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-md-8 mb-auto p-5">

                    <div class="card">

                        <div class="card-header">Compensate time of all active subscriptions(s)</div>

                        <div class="card-body">

                            <form method="POST">

                                <div class="form-group row">

                                    <label for="subscription" class="col-md-4 col-form-label text-md-right">Subscription</label>

                                    <div class="col-md-6">

                                        <select class="form-control" name="subscription" id="subscription">

                                           <?php

                                           $result = $conn->query("SELECT * FROM `products`");

                                           while ($row = $result->fetch_assoc()) {

                                                echo "<option value=\"{$row['product']}\">{$row['product']}</option>";
                                            }

                                            ?>

                                        </select>

                                    </div>

                                </div>

                                <div class="card-body">

                                    <form method="POST">

                                        <div class="form-group row">

                                            <label for="hours" class="col-md-4 col-form-label text-md-right">Hours</label>

                                            <div class="col-md-6">

                                                <input id="hours" type="text" class="form-control" name="hours" required="" placeholder="Amount of hours.">

                                            </div>

                                        </div>

                                        <div class="mt-4 form-group d-flex justify-content-center">

                                            <button id="compensateall" name="compensateall" type="submit" class="btn btn-primary btn-md">

                                                Compensate

                                            </button>

                                        </div>

                                    </form>

                                </div>

                        </div>

                    </div>

                </div>

            </div>

</html>