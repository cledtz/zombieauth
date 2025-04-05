<?php
include '../../conn/conn.php';

$client_encryption_key;

// DO NOT FORGET TO CHANGE _GET TO _POST AFTER TESTING!!!!!!!!!!!!!!!

if (isset($_POST['iv']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['product']) && isset($_POST['request']))
{
    $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $createdAt = new DateTime($row['session_created_at']);
    $now = new DateTime();
    $interval = $now->diff($createdAt);

    if ($interval->i >= 10)
    {
        die();
    }

    $encryption_key = $row['auth_encryption_hash'];
    $stream_encryption_key = $row['stream_encryption_hash'];

    if ($result->num_rows == NULL)
    {
        die();
    }

    $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($result->num_rows == 0)
    {
        die(); //invalid username = return null
    }
    if ($row['password'] != $_POST['password'])
    {
        die(); //invalid password = return null
    }
    if ($row['ban_reason'] != NULL)
    {
        die(); //banned = return null
    }

    //licenses hold on user account

    $stmta = $conn->prepare("SELECT * FROM `subscriptions` WHERE `user` = ?");
    $stmta->bind_param("s", $_POST['username']);
    $stmta->execute();
    $resulta = $stmta->get_result();

    $sub_okay = false;
    while ($rowa = $resulta->fetch_assoc())
    {
        if ($_POST['product'] == $rowa['subscription'])
        {
            $current_time = date('d-m-Y H:i:s');
            $current_now_time = new DateTime($current_time);
            if ($rowa['expires_at'] != NULL)
            {
                $server_expires_at_time = new DateTime($rowa['expires_at']);
            }
            if ($rowa['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
            {
                die(); //expired sub = return null
            }
            else
            {
                $sub_okay = true;
                //echo $row['subscription'] . "," . $row['expires_at'] . ";";
                break;
            }
        }
    }

    $stmtab = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
    $stmtab->bind_param("s", $_POST['product']);
    $stmtab->execute();
    $resultab = $stmtab->get_result();
    $rowab = $resultab->fetch_assoc();
    if ($resultab->num_rows == 0)
    {
        die();
    }

    if ($_POST['request'] == "Module")
    {
        $binary = "null";
        $method = 'aes-256-cfb';

        $path = "C:\\Builds\\" . $_POST['product'] . "\\" . $_POST['username'] . ".dll";

        if ($sub_okay == true)
        {
            if (file_exists($path))
            {
                $filesize = filesize($path);
                $fp = fopen($path, 'rb');
                $binary = fread($fp, $filesize);
                fclose($fp);
            }
            else
                die();
        }

        //$returnserver = base64_decode(openssl_decrypt(base64_decode($binary), $method, "98G2Lu7vyf62lvM6xE85CvE8w3ws8a37", true, "63gNJ8BbdDoX43sJ"));

        $returnserveren = base64_encode($binary);

        $returntoclient = base64_encode(openssl_encrypt($returnserveren, $method, $stream_encryption_key, true, $_POST['iv']));

        die($returntoclient);
    }
    else
    {
        $binary = "null";
        $method = 'aes-256-cfb';

        $file_extension = ".sys";
        if ($_POST['request'] != "Driver")
            $file_extension = ".dll";

        $path = "C:\\Builds\\" . $_POST['request'] . "\\" . $_POST['username'] . $file_extension;

        if (file_exists($path))
        {
            $filesize = filesize($path);
            $fp = fopen($path, 'rb');
            $binary = fread($fp, $filesize);
            fclose($fp);
        }
        else
            die();

        //$returnserver = base64_decode(openssl_decrypt(base64_decode($binary), $method, "98G2Lu7vyf62lvM6xE85CvE8w3ws8a37", true, "63gNJ8BbdDoX43sJ"));
        $returnserveren = base64_encode($binary);

        $returntoclient = base64_encode(openssl_encrypt($returnserveren, $method, $stream_encryption_key, true, $_POST['iv']));
        die($returntoclient);
    }
}
else
{

    if (isset($_GET['securekey']) && isset($_GET['request']))
    {

        if ($_GET['securekey'] == "fFCSYNMH9MF3LTohPytlye6DBGxtMvyNIGGAjFzQRo")
        {
            if ($_GET['request'] == "rotate")
            {
                $client_encryption_key = generate_mixed(32);
                $fp1 = fopen("8e4w5148ed84w8edq98dw4Q89WS85598QQWSQWE5W6D2", "wb");
                fwrite($fp1, $client_encryption_key);
                fclose($fp1);
                die($client_encryption_key);
            }
        }
    }

    header("Location: https://google.com");
}
