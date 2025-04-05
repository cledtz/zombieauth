<?php

include '../conn/conn.php';

session_start();



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



if (!isset($_SESSION['username'])) {

    header("Location: /");

    exit();
}



if (isset($_POST['quantity']) && isset($_POST['role'])) {

    for ($i = 0; $i < $_POST['quantity']; $i++) {

        $creator = $_SESSION['username'];

        $role = $_POST['role'];

        $license = rndstringInvites();



        $stmt = $conn->prepare("INSERT INTO `invites` (`invite`, `role`, `creator`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $license, $role, $creator);
        $stmt->execute();
        
    }
}







?>



<html>



<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Generate invites</title>

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

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-md-8 mb-auto p-5">

                    <div class="card">

                        <div class="card-header">Create registration invites</div>



                        <div class="card-body">

                            <form method="POST">



                                <!-- <div class="form-group row">

                                    <label for="owner" class="col-md-4 col-form-label text-md-right">License

                                        owner</label>

                                    <div class="col-md-6">

                                        <select class="form-control" name="owner" id="owner">

                                            <?php

                                            $result = $conn->query("SELECT * FROM `accounts`");

                                            while ($row = $result->fetch_assoc()) {

                                                echo "<option value=\"{$row['uid']}\">{$row['username']}</option>";
                                            }

                                            ?>

                                        </select>

                                    </div>

                                </div> -->



                                <div class="form-group row">

                                    <label for="role" class="col-md-4 col-form-label text-md-right">Role</label>

                                    <div class="col-md-6">

                                        <select class="form-control" name="role" id="role">

                                            <option value="reseller">Reseller</option>
                                            <option value="management">Management</option>
                                            <option value="administrator">Administrator</option>

                                        </select>

                                    </div>

                                </div>



                                <div class="form-group row">

                                    <label for="quantity" class="col-md-4 col-form-label text-md-right">Quantity</label>

                                    <div class="col-md-6">

                                        <input id="quantity" value="1" type="number" min="1" max="100" class="form-control" name="quantity" required="">

                                    </div>

                                </div>



                                <div class="form-group d-flex justify-content-center">

                                    <button type="submit" class="btn btn-primary btn-md">

                                        Generate

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