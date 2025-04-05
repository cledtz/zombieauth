<?php
include 'panel/conn/conn.php';
if (isset($_GET['action']))
{
    if ($_GET['action'] == "launcher")
    {
        die("");
    }
    else if ($_GET['action'] == "launcher_version")
    {
        die("1.0");
    }
    else if ($_GET['action'] == "client_version")
    {
        prt_static("1.0");
    }
}
else
{
    header("Location: panel");
}

?>