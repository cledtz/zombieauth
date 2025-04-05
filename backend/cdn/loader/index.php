<?php
include "../../main_client/panel/conn/conn.php";

$loader_url = 'https://cdn.combiq.co/loader/232353454355.exe';
$efi_url = 'https://cdn.combiq.co/loader/ventro-1387057f-6f7a-4fa1-afe2-f21f3e145e7d.zip';


if (isset($_GET['request'])) 
{
    if ($_GET['request'] == "loader") 
    {
        //die($loader_url);
        $file_url = $loader_url;
        $file_name = rndstring4(32) . '.exe';
        $file_content = file_get_contents($file_url);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . strlen($file_content));
        ob_clean();  // Clear output buffer
        flush();     // Flush output buffer

        die($file_content);
    }

    if ($_GET['request'] == "loader_auth" && isset($_GET['license'])) 
    {
        $success = true;
        $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
        $stmt->bind_param("s", $_GET['license']);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        if ($result->num_rows == 0) {
            // echo '<div class="alert alert-danger" role="alert">Invalid license key.</div>';
            die("Error");
            $success = false;
        }
    
        if ($success) 
        {
            if ($row['ban_reason'] != NULL) {
                // echo '<div class="alert alert-danger" role="alert">Banned license key.</div>';
                die("Error");
                $success = false;
            }
        }
    
        if ($success) {
            $file_url = $loader_url;
            $file_name = rndstring4(32) . '.exe';
            $file_content = file_get_contents($file_url);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Content-Length: ' . strlen($file_content));
            ob_clean();  // Clear output buffer
            flush();     // Flush output buffer
    
            die($file_content);
        }
    }

    if ($_GET['request'] == "efi_auth" && isset($_GET['license'])) 
    {
        $success = true;
        $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
        $stmt->bind_param("s", $_GET['license']);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        if ($result->num_rows == 0) {
            // echo '<div class="alert alert-danger" role="alert">Invalid license key.</div>';
            die("Error");
            $success = false;
        }
    
        if ($success) 
        {
            if ($row['ban_reason'] != NULL) {
                // echo '<div class="alert alert-danger" role="alert">Banned license key.</div>';
                die("Error");
                $success = false;
            }
        }
    
        if ($success) {
            $file_url = $efi_url;
            $file_name = rndstring4(32) . '.zip';
            $file_content = file_get_contents($file_url);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Content-Length: ' . strlen($file_content));
            ob_clean();  // Clear output buffer
            flush();     // Flush output buffer
    
            die($file_content);
        }
    }
}

if (isset($_POST['download']) && isset($_POST['license'])) {
    $success = true;
    $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
    $stmt->bind_param("s", $_POST['license']);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($result->num_rows == 0) {
        echo '<div class="alert alert-danger" role="alert">Invalid license key.</div>';
        $success = false;
    }

    if ($success) {
        if ($row['ban_reason'] != NULL) {
            echo '<div class="alert alert-danger" role="alert">Banned license key.</div>';
            $success = false;
        }

        // if ($row['user'] != NULL) {
        //     echo '<div class="alert alert-danger" role="alert">License has already been redeemed.</div>';
        //     $success = false;
        // }
    }

    if ($success) {
        $file_url = $loader_url;
        $file_name = rndstring4(32) . '.exe';
        $file_content = file_get_contents($file_url);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . strlen($file_content));
        ob_clean();  // Clear output buffer
        flush();     // Flush output buffer

        die($file_content);
    }

    //die("Please join our discord by visiting discord.combiq.co | Download the loader by sending our bot the /loader command.");
}
?>