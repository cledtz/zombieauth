<?php

include '../conn/conn.php';

if (isset($_GET['action']))
{
    if ($_GET['action'] == "reset_hwid")
    {
        if (isset($_GET['license']) && isset($_GET['owner']) && isset($_GET['api']))
        {
            if ($_GET['api'] != "e4b7b6206ac87de1193b7ab960eb27a0a42591094edda8c362dc2705865c162b")
            {
                die("APIkey failure");
            }

            $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ? AND `owner` = ?");
            $stmt->bind_param("ss", $_GET['license'], $_GET['owner']);
            $stmt->execute();
            $resultlicense = $stmt->get_result();

            if ($resultlicense->num_rows == 0)
            {
                die("License is not owned by: " . $_GET['owner']);
            }
            else
            {
                $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
                $stmt->bind_param("s", $_GET['license']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($result->num_rows < 1)
                {
                    die("Incorrect license.");
                }
                else
                {
                    $user = $row['user'];

                    $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
                    $stmt->bind_param("s", $user);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if ($result->num_rows == NULL)
                    {
                        die("Such account does not exist.");
                    }

                    if ($row['ban_reason'] != NULL)
                    {
                        die("Account has been banned. Reason: " . $row['ban_reason']);
                    }

                    $null = NULL;
                    $stmt4566 = $conn->prepare("UPDATE `user` SET `hwid` = ? WHERE `username` = ?");
                    $stmt4566->bind_param("ss", $null, $user);
                    $stmt4566->execute();
                    die("Hardware-id has been reset.");
                }
            }
        }
    }
    else if ($_GET['action'] == "generate")
    {
        if (isset($_GET['quantity']) && isset($_GET['product']) && isset($_GET['hours']) && isset($_GET['licenseowner']) && isset($_GET['api']))
        {
            if ($_GET['api'] != "e4b7b6206ac87de1193b7ab960eb27a0a42591094edda8c362dc2705865c162b")
            {
                die("APIkey failure");
            }

            $stmt = $conn->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['licenseowner']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $product = $_GET['product'];
            $owner = $_GET['licenseowner'];
            $creator = $_GET['licenseowner'];
            $hours = $_GET['hours'];

            if (!is_numeric($_GET['quantity']) || !is_numeric($_GET['hours']))
            {
                die("Value must be an integer. (Numbers only!)");
            }

            for ($i = 0; $i < $_GET['quantity']; $i++)
            {
                startgen:

                $license = rndstring();

                $stmtl = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
                $stmtl->bind_param("s", $license);
                $stmtl->execute();
                $resultl = $stmtl->get_result();
                if ($resultl->num_rows > 0)
                {
                    goto startgen;
                }

                $stmt = $conn->prepare("INSERT INTO `licenses` (`license`, `hours`, `product`, `creator`, `owner`) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $license, $hours, $product, $creator, $owner);
                $stmt->execute();
            }

            die("License(s) have been added to your panel account.");
        }
        else
        {
            die("Something went wrong. G1");
        }
    }
    else
    {
        die("Something went wrong.");
    }
}
else
{
    die("API failure: Action type missing");
}
