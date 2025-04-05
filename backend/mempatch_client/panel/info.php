<?php

include 'conn/conn.php';



session_start();



if (!isset($_SESSION['username'])) {

    header("Location: /");

    exit();

}



die("perhaps in the future XD");


?>