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

if ($row['role'] != 'administrator') {

    $_SESSION['errormessage'] = "You are not allowed to be on this site.";

    header("Location: ../dashboard.php");

    exit();
}


if (isset($_GET['invite']) && isset($_POST['ban']) && isset($_POST['ban_reason'])) {

    $stmt = $conn->prepare("SELECT * FROM `invites` WHERE `invite` = ?");
    $stmt->bind_param("s", $_POST['invite']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    



    if ($result->num_rows < 1) {

        echo '<div class="alert alert-danger" role="alert">Invite not found!</div>';
    }



    $invite = $_POST['invite'];

    $reason = $_POST['reason'];



    $stmt = $conn->prepare("UPDATE `invites` SET `ban_reason` = ? WHERE `invite` = ?");
    $stmt->bind_param("ss", $reason, $invite);
    $stmt->execute();
    

    echo '<div class="alert alert-info" role="alert">Invite has been banned!</div>';
}

?>

<html>

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Invites</title>

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

                        if ($row['role'] == "administrator") 
                        {

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

        <div class="mt-3">

            <h5 class="md-3">Showing info for

                <?= $conn->query("SELECT COUNT(*) FROM `invites`")->fetch_assoc()['COUNT(*)'];  ?> invites:</h5>

            <table class="table table-hover table-bordered mt-4">

                <thead>

                    <tr>

                        <th class="unselectable" scope="col">ID</th>

                        <th class="unselectable" scope="col">Invites</th>

                        <th class="unselectable" scope="col">Creator</th>

                        <th class="unselectable" score="col">Role</th>

                        <th class="unselectable" score="col">Ban reason</th>

                        <!-- <th class="unselectable" scope="col">Redeemed by</th> -->

                    </tr>

                </thead>

                <tbody>

                    <?php

                    $result = $conn->query("SELECT * FROM `invites`");

                    while ($row = $result->fetch_assoc()) {

                        $role = $row['role'];

                        $active = $row['used'];

                        $creator = $row['creator'];

                        $banned = "";

                        if ($row['creator'] == "") $creator = "root";

                        if ($row['ban_reason'] != NULL) {

                            $banned = $row['ban_reason'];
                        } else {

                            $banned = "-";
                        }

                        if ($row['used'] == "0") {

                            $active = "-";

                            if ($row['ban_reason'] != NULL) {

                                echo '

                                            <tr class="table-danger">

                                            <th scope="row" class="unselectable">' . $row['id'] . '</th>

                                            <td>' . $row['invite'] . '</td>

                                            <td class="unselectable">' . $creator . '</td>

                                            <td class="unselectable">' . $role . '</td>

                                            <td class="unselectable">' . $banned . '</td>

                                            </tr>';
                            } else {

                                echo '

                                            <th scope="row" class="unselectable">' . $row['id'] . '</th>

                                            <td>' . $row['invite'] . '</td>

                                            <td class="unselectable">' . $creator . '</td>

                                            <td class="unselectable">' . $role . '</td>

                                            <td class="unselectable">' . $banned . '</td>

                                            </tr>';
                            }
                        } else if ($row['used'] == "1") {

                            $active = "Yes";

                            if ($row['ban_reason'] != NULL) {

                                echo '

                                            <tr class="table-danger">

                                            <th scope="row" class="unselectable">' . $row['id'] . '</th>

                                            <td>' . $row['invite'] . '</td>

                                            <td class="unselectable">' . $creator . '</td>

                                            <td class="unselectable">' . $role . '</td>

                                            <td class="unselectable">' . $banned . '</td>

                                            </tr>';
                            } else {

                                echo '

                                            <tr class="table-warning">

                                            <th scope="row" class="unselectable">' . $row['id'] . '</th>

                                            <td>' . $row['invite'] . '</td>

                                            <td class="unselectable">' . $creator . '</td>

                                            <td class="unselectable">' . $role . '</td>

                                            <td class="unselectable">' . $banned . '</td>

                                            </tr>';
                            }
                        }
                    }

                    ?>

                </tbody>

            </table>

        </div>

    </div>







    <style>
        .blur {

            filter: blur(3px);

        }

        .blur:hover {

            filter: blur(0px);

            transition: .5s;

        }
    </style>

</body>



</html>