<?php
include '../../conn/conn.php';
session_start();
?>
<!DOCTYPE html>
<html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>File upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous">
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.1/mdb.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mb-auto p-5">
                <div class="card">
                    <div class="card-header">Upload</div>
                    <div class="card-body">
                        <form method="POST" action="upload.php" enctype="multipart/form-data">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right" for="customFile">Upload</label>
                                <div class="col-md-6">
                                    <input type="file" class="form-control" id="customFile" name="uploadedFile">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label for="proid" class="col-md-4 col-form-label text-md-right">Product-ID</label>

                                <div class="col-md-6">

                                    <select class="form-control" name="proid" id="proid">

                                    <option value="Client">Utility: Client</option>
                                    <option value="Injector">Utility: Injector</option>
                                    <option value="Driver">Utility: Driver</option>
                                        <?php

                                        $result = $conn->query("SELECT * FROM `products`");

                                        while ($row = $result->fetch_assoc()) {

                                            echo "<option value=\"{$row['product']}\">Product: {$row['product']}</option>";
                                        }

                                        ?>

                                    </select>

                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right" for="password">Developer-ID</label>
                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="pwd" required="" placeholder="">
                                </div>
                            </div>
                            <div class="form-group d-flex justify-content-center">
                                <button name="uploadBtn" value="Upload" type="submit" class="btn btn-primary btn-lg justify-content-center btn-block">
                                    Upload </button>
                            </div> <?php
                                    if (isset($_SESSION['message']) && $_SESSION['message']) {
                                        echo '
													<center>
														<div class="alert alert-light" role="alert">' . $_SESSION['message'] . '</div>
													</center>
                                        ';
                                        unset($_SESSION['message']);
                                    }
                                    ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>