<?php

include '../conn/conn.php';

if (isset($_GET['action']))
{
    if ($_GET['action'] == "register")
    {
        if (isset($_GET['username']) && isset($_GET['password']) && isset($_GET['hwid']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows > 0)
            {
                prt_static("Please choose a different username.", $_GET['username']);
            }

            if (str_contains($_GET['username'], "admin") || str_contains($_GET['username'], "Admin") || str_contains($_GET['username'], "cledtz"))
            {
                prt_static("Username contains unallowed phrases. Please choose a different one.", $_GET['username']);
            }

            $stmta = $conn->prepare("SELECT * FROM `user` WHERE `hwid` = ?");
            $stmta->bind_param("s", $_GET['hwid']);
            $stmta->execute();
            $resulta = $stmta->get_result();
            $rowa = $resulta->fetch_assoc();

            if ($resulta->num_rows > 0)
            {
                prt_static("Hardware-id is already bound to another account. (Username: " . $rowa['username'] . ")! Please login, if you do not recognize such account or forgot your password please contact an administrator!", $_GET['username']);
            }

            $stmt = $conn->prepare("INSERT INTO `user` (`username`, `password`, `hwid`) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_GET['username'], $_GET['password'], $_GET['hwid']);
            $stmt->execute();

            prt_static("Success", $_GET['username']);
        }
    }
    if ($_GET['action'] == "login")
    {
        $encryption_key;
        if (isset($_GET['username']) && isset($_GET['password']) && isset($_GET['hwid']) && isset($_GET['discord_id']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $createdAt = new DateTime($row['session_created_at']);
            $now = new DateTime();
            $interval = $now->diff($createdAt);

            if ($interval->i >= 10)
            {
                die("Session has expired. Please restart the loader.");
            }

            $encryption_key = $row['auth_encryption_hash'];

            if ($result->num_rows == NULL)
            {
                prt("Such account does not exist.", $encryption_key, $_GET['username']);
            }

            if ($row['password'] == NULL)
            {
                $stmt5 = $conn->prepare("UPDATE `user` SET `password` = ? WHERE `username` = ?");
                $stmt5->bind_param("ss", $_GET['password'], $_GET['username']);
                $stmt5->execute();

                prt("New password set, please login now.", $encryption_key, $_GET['username']);
            }

            if ($row['password'] != $_GET['password'])
            {
                prt("Password does not match with our records. If you forgot your password please contact an administrator!", $encryption_key, $_GET['username']);
            }

            if ($row['ban_reason'] != NULL)
            {
                prt("Account has been banned. Reason: " . $row['ban_reason'], $encryption_key, $_GET['username']);
            }

            if ($row['hwid'] != NULL && $row['hwid'] != $_GET['hwid'])
            {
                prt("Hardware-id does not match with our records.", $encryption_key, $_GET['username']);
            }

            if ($row['hwid'] == NULL)
            {
                $stmt4 = $conn->prepare("UPDATE `user` SET `hwid` = ? WHERE `username` = ?");
                $stmt4->bind_param("ss", $_GET['hwid'], $_GET['username']);
                $stmt4->execute();
            }

            $stmt5 = $conn->prepare("UPDATE `user` SET `discord_id` = ? WHERE `username` = ?");
            $stmt5->bind_param("ss", $_GET['discord_id'], $_GET['username']);
            $stmt5->execute();

            //licenses hold on user account

            $stmt = $conn->prepare("SELECT * FROM `subscriptions` WHERE `user` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();

            $echos = "Success&";

            while ($row = $result->fetch_assoc())
            {
                $current_time = date('d-m-Y H:i:s');
                $current_now_time = new DateTime($current_time);
                if ($row['expires_at'] != NULL)
                {
                    $server_expires_at_time = new DateTime($row['expires_at']);
                }
                if ($row['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
                {
                    //echo $row['subscription'] .",Expired,-,-;";
                }
                else
                {
                    $stmta = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
                    $stmta->bind_param("s", $row['subscription']);
                    $stmta->execute();
                    $resulta = $stmta->get_result();
                    $rowa = $resulta->fetch_assoc();

                    $now = new DateTime();


                    $last_updated;
                    $timestamp_u = DateTime::createFromFormat('d-m-Y H:i:s', $rowa['last_upload']);
                    $diff_u = $now->diff($timestamp_u);
                    if ($diff_u->y)
                    {
                        $last_updated = $diff_u->y . " year";
                    }
                    else if ($diff_u->m)
                    {
                        $last_updated = $diff_u->m . " month";
                    }
                    else if ($diff_u->d)
                    {
                        $last_updated = $diff_u->d . " day";
                    }
                    else if ($diff_u->h)
                    {
                        $last_updated = $diff_u->h . " hour";
                    }
                    else if ($diff_u->i)
                    {
                        $last_updated = $diff_u->i . " minute";
                    }
                    else if ($diff_u->s)
                    {
                        $last_updated = $diff_u->s . " second";
                    }


                    $timestamp = DateTime::createFromFormat('d-m-Y H:i:s', $row['expires_at']);
                    $diff = $now->diff($timestamp);
                    $expiration;
                    if ($diff->y)
                    {
                        $expiration = $diff->y . " year";
                    }
                    else if ($diff->m)
                    {
                        $expiration = $diff->m . " month";
                    }
                    else if ($diff->d)
                    {
                        $expiration = $diff->d . " day";
                    }
                    else if ($diff->h)
                    {
                        $expiration = $diff->h . " hour";
                    }
                    else if ($diff->i)
                    {
                        $expiration = $diff->i . " minute";
                    }
                    else if ($diff->s)
                    {
                        $expiration = $diff->s . " second";
                    }

                    //echo $row['subscription'] . ",Expires in: " . $expiration  . ",Status: " . $rowa['status'] . ",Last updated: " . $last_updated . ";";

                    $echos .= serialize_xss($row['subscription']) . "," . serialize_xss($expiration) . "," . serialize_xss($rowa['status']) . "," . serialize_xss($last_updated) . ";";
                }
            }

            prt($echos, $encryption_key, $_GET['username']);
        }
    }
    if ($_GET['action'] == "redeem_license")
    {
        if (isset($_GET['license']) && isset($_GET['username']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $createdAt = new DateTime($row['session_created_at']);
            $now = new DateTime();
            $interval = $now->diff($createdAt);

            if ($interval->i >= 10)
            {
                die("Session has expired. Please restart the loader.");
            }

            $encryption_key = $row['auth_encryption_hash'];

            if ($result->num_rows == NULL)
            {
                prt("Such account does not exist.", $encryption_key, $_GET['username']);
            }

            $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
            $stmt->bind_param("s", $_GET['license']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $current_time = date('d-m-Y H:i:s');
            $current_now_time = new DateTime($current_time);
            if ($result->num_rows == 0)
            {
                prt("Incorrect license.", $encryption_key, $_GET['username']);
            }
            if ($row['ban_reason'] != NULL)
            {
                prt("License has been banned. Reason: " . $row['ban_reason'], $encryption_key, $_GET['username']);
            }
            if ($row['user'] != NULL)
            {
                prt("License was already redeemed.", $encryption_key, $_GET['username']);
            }

            //SUCCESS
            if ($row['redeemed_at'] == NULL)
            {
                $stmt3 = $conn->prepare("UPDATE `licenses` SET `redeemed_at` = ? WHERE `license` = ?");
                $stmt3->bind_param("ss", $current_time, $_GET['license']);
                $stmt3->execute();
            }

            if ($row['user'] == NULL)
            {
                $stmt5 = $conn->prepare("UPDATE `licenses` SET `user` = ? WHERE `license` = ?");
                $stmt5->bind_param("ss", $_GET['username'], $_GET['license']);
                $stmt5->execute();
            }

            $stmta = $conn->prepare("SELECT * FROM `subscriptions` WHERE `user` = ? AND `subscription` = ?");
            $stmta->bind_param("ss", $_GET['username'], $row['product']);
            $stmta->execute();
            $resulta = $stmta->get_result();
            $rowa = $resulta->fetch_assoc();
            if ($resulta->num_rows == 0)
            {
                $duration = $row['hours'];
                $duration2 = $duration . ' hours';
                $expiration = date('d-m-Y H:i:s', strtotime($current_time . ' + ' . $duration2));

                $stmtb = $conn->prepare("INSERT INTO `subscriptions` (`subscription`, `expires_at`, `user`) VALUES (?, ?, ?)");
                $stmtb->bind_param("sss", $row['product'], $expiration, $_GET['username']);
                $stmtb->execute();

                prt("Success", $encryption_key, $_GET['username']);
            }
            else
            {
                $server_expires_at_time;
                if ($rowa['expires_at'] != NULL)
                {
                    $server_expires_at_time = new DateTime($rowa['expires_at']);
                }

                if ($rowa['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
                {
                    $NULL = NULL;
                    $stmtb = $conn->prepare("UPDATE `subscriptions` SET `expires_at` = ? WHERE `user` = ? AND `subscription` = ?");
                    $stmtb->bind_param("sss", $NULL, $_GET['username'], $row['product']);
                    $stmtb->execute();

                    $duration = $row['hours'];
                    $duration2 = $duration . ' hours';
                    $expiration = date('d-m-Y H:i:s', strtotime($current_time . ' + ' . $duration2));

                    $stmtb = $conn->prepare("UPDATE `subscriptions` SET `expires_at` = ? WHERE `user` = ? AND `subscription` = ?");
                    $stmtb->bind_param("sss", $expiration, $_GET['username'], $row['product']);
                    $stmtb->execute();
                }
                else
                {
                    $duration = $row['hours'];
                    $duration2 = $duration . ' hours';
                    $expiration = date('d-m-Y H:i:s', strtotime($rowa['expires_at'] . ' + ' . $duration2));

                    $stmtb = $conn->prepare("UPDATE `subscriptions` SET `expires_at` = ? WHERE `user` = ? AND `subscription` = ?");
                    $stmtb->bind_param("sss", $expiration, $_GET['username'], $row['product']);
                    $stmtb->execute();
                }

                prt("Success", $encryption_key, $_GET['username']);
            }
        }
    }
    else if ($_GET['action'] == "create_session")
    {
        if (isset($_GET['username']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($result->num_rows == 0)
            {
                prt_static("Such account does not exist.", $_GET['username']);
            }

            $username = $_GET['username'];
            $rnd1 = rndstring_aes(32);
            $rnd2 = rndstring_aes(32);

            $current_time = date('d-m-Y H:i:s');


            $stmtb = $conn->prepare("UPDATE `user` SET `session_created_at` = ?, `auth_encryption_hash` = ?,  `stream_encryption_hash` = ?  WHERE `username` = ?");
            $stmtb->bind_param("ssss", $current_time, $rnd1, $rnd2, $username);
            $stmtb->execute();

            prt_static($rnd1 . ":" . $rnd2, $_GET['username']);
        }
    }
    else if ($_GET['action'] == "request_custom_build")
    {
        if (isset($_GET["module"]) == "Driver")
        {
            if ($_GET['status'] == "complete")
            {
                if (isset($_GET['module']))
                {
                    if ($_GET['module'] == "Driver")
                    {
                        $NULL = NULL;
                        $Driver_txt = "Driver";
                        $stmtb = $conn->prepare("UPDATE `custom_builds` SET `created_at` = ?, `status` = ? WHERE `username` = ? AND `product` = ?");
                        $stmtb->bind_param("ssss", $current_time, $NULL, $_GET['username'], $Driver_txt);
                        $stmtb->execute();

                        die("Success:Completed3");
                    }
                }
            }

        }
        $encryption_key;
        if (isset($_GET['username']) && isset($_GET['subscription']) && isset($_GET['status']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $createdAt = new DateTime($row['session_created_at']);
            $now = new DateTime();
            $interval = $now->diff($createdAt);

            if ($interval->i >= 10)
            {
                die("Session has expired. Please restart the loader.");
            }

            $encryption_key = $row['auth_encryption_hash'];

            if ($result->num_rows == NULL)
            {
                prt("Such account does not exist.", $encryption_key, $_GET['username']);
            }

            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == NULL)
            {
                prt("Such account does not exist.", $encryption_key, $_GET['username']);
            }

            if ($row['ban_reason'] != NULL)
            {
                prt("Account has been banned. Reason: " . $row['ban_reason'], $encryption_key, $_GET['username']);
            }

            //licenses hold on user account

            $stmt = $conn->prepare("SELECT * FROM `subscriptions` WHERE `user` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc())
            {
                $current_time = date('d-m-Y H:i:s');
                $current_now_time = new DateTime($current_time);
                if ($row['expires_at'] != NULL)
                {
                    $server_expires_at_time = new DateTime($row['expires_at']);
                }
                if ($row['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
                {
                    //echo $row['subscription'] .",Expired,-,-;";
                }
                else
                {
                    if ($_GET['subscription'] == $row['subscription'])
                    {
                        if ($_GET['status'] == "add")
                        {
                            $stmta = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
                            $stmta->bind_param("s", $_GET['subscription']);
                            $stmta->execute();
                            $resulta = $stmta->get_result();
                            $rowa = $resulta->fetch_assoc();
                            if ($resulta->num_rows == NULL)
                            {
                                prt("Such subscription does not exist.", $encryption_key, $_GET['username']);
                            }

                            $file_extension = pathinfo($rowa['file_name'], PATHINFO_EXTENSION);

                            //cheat
                            {
                                if (!file_exists("C:\\Builds\\" . $_GET['subscription'] . "\\" . $_GET['username'] . "." . $file_extension))
                                {
                                    $stmt = $conn->prepare("SELECT * FROM `custom_builds` WHERE `username` = ? AND `product` = ?");
                                    $stmt->bind_param("ss", $_GET['username'], $_GET['subscription']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = $result->fetch_assoc();

                                    $username = $_GET['username'];
                                    $current_time = date('d-m-Y H:i:s');
                                    $NULL = NULL;

                                    if ($result->num_rows == 0)
                                    {
                                        $status = "Waiting";
                                        $stmtb = $conn->prepare("INSERT INTO `custom_builds` (`username`, `product`, `created_at`, `status`) VALUES (?, ?, ?, ?)");
                                        $stmtb->bind_param("ssss", $username, $_GET['subscription'], $current_time, $status);
                                        $stmtb->execute();
                                        //prt("Success:Added", $encryption_key);
                                        //prt_static($rnd1.":".$rnd2);
                                    }
                                    else
                                    {
                                        if ($row['status'] == NULL)
                                        {
                                            $status = "Waiting";
                                            $stmtb = $conn->prepare("UPDATE `custom_builds` SET `created_at` = ?, `status` = ? WHERE `username` = ? AND `product` = ?");
                                            $stmtb->bind_param("ssss", $current_time, $status, $username, $_GET['subscription']);
                                            $stmtb->execute();

                                            //prt("Success:Updated", $encryption_key);
                                        }
                                    }
                                }
                            }

                            //driver
                            {

                                $string = $rowa['InjectorMethod'];
                                if (strpos($string, "Driver") !== false)
                                {
                                    if (!file_exists("C:\\Builds\\Driver" . "\\" . $_GET['username'] . ".sys"))
                                    {
                                        $driver_text = "Driver";
                                        $stmt = $conn->prepare("SELECT * FROM `custom_builds` WHERE `username` = ? AND `product` = ?");
                                        $stmt->bind_param("ss", $_GET['username'], $driver_text);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $row = $result->fetch_assoc();

                                        $username = $_GET['username'];
                                        $current_time = date('d-m-Y H:i:s');
                                        $NULL = NULL;

                                        if ($result->num_rows == 0)
                                        {
                                            $status = "Waiting";
                                            $stmtb = $conn->prepare("INSERT INTO `custom_builds` (`username`, `product`, `created_at`, `status`) VALUES (?, ?, ?, ?)");
                                            $stmtb->bind_param("ssss", $username, $driver_text, $current_time, $status);
                                            $stmtb->execute();
                                            //prt("Success:Added", $encryption_key);
                                            //prt_static($rnd1.":".$rnd2);
                                        }
                                        else
                                        {
                                            if ($row['status'] == NULL)
                                            {
                                                $status = "Waiting";
                                                $stmtb = $conn->prepare("UPDATE `custom_builds` SET `created_at` = ?, `status` = ? WHERE `username` = ? AND `product` = ?");
                                                $stmtb->bind_param("ssss", $current_time, $status, $username, $driver_text);
                                                $stmtb->execute();

                                                //prt("Success:Updated", $encryption_key);
                                            }
                                        }
                                    }
                                }
                            }

                            prt("Done.", $encryption_key, $_GET['username']);
                        }
                        else if ($_GET['status'] == "check")
                        {
                            $stmta = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
                            $stmta->bind_param("s", $_GET['subscription']);
                            $stmta->execute();
                            $resulta = $stmta->get_result();
                            $rowa = $resulta->fetch_assoc();
                            if ($resulta->num_rows == NULL)
                            {
                                prt("Such subscription does not exist.", $encryption_key, $_GET['username']);
                            }

                            $file_extension = pathinfo($rowa['file_name'], PATHINFO_EXTENSION);

                            $stmt = $conn->prepare("SELECT * FROM `custom_builds` WHERE `username` = ? AND `product` = ?");
                            $stmt->bind_param("ss", $_GET['username'], $_GET['subscription']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();

                            if (file_exists("C:\\Builds\\" . $_GET['subscription'] . "\\" . $_GET['username'] . "." . $file_extension) && $row['status'] != "Waiting")
                            {
                                //prt("Success", $encryption_key);
                            }
                            else
                            {
                                prt("Error", $encryption_key, $_GET['username']);
                            }

                            $string = $rowa['InjectorMethod'];
                            if (strpos($string, "Driver") !== false)
                            {
                                if (file_exists("C:\\Builds\\Driver" . "\\" . $_GET['username'] . ".sys"))
                                {
                                    //prt("Success", $encryption_key);
                                }
                                else
                                {
                                    prt("Error", $encryption_key, $_GET['username']);
                                }
                            }

                            prt("Success", $encryption_key, $_GET['username']);
                        }
                        else if ($_GET['status'] == "complete")
                        {
                            $NULL = NULL;
                            $stmtb = $conn->prepare("UPDATE `custom_builds` SET `created_at` = ?, `status` = ? WHERE `username` = ? AND `product` = ?");
                            $stmtb->bind_param("ssss", $current_time, $NULL, $_GET['username'], $_GET['subscription']);
                            $stmtb->execute();

                            prt("Success:Completed", $encryption_key, $_GET['username']);
                        }
                    }
                }
            }

            prt("Error", $encryption_key, $_GET['username']);
        }
    }
    else if ($_GET['action'] == "list_custom_builds")
    {
        if (isset($_GET['api']))
        {
            if ($_GET['api'] != "e4b7b6206ac87de1193b7ab960eb27a0a42591094edda8c362dc2705865c162b")
            {
                die("APIkey failure");
            }

            $result = $conn->query("SELECT * FROM `custom_builds` WHERE `status` = 'Waiting' LIMIT 1");
            $row = $result->fetch_assoc();
            if ($result->num_rows == 0)
            {
                die("None");
            }
            $username = $row['username'];

            if ($row['product'] != "Driver")
            {
                $stmt = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
                $stmt->bind_param("s", $row['product']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($result->num_rows == 0)
                {
                    die("Something failed. #459859859");
                }

                $extension = "." . pathinfo($row['file_name'], PATHINFO_EXTENSION);

                die($row['product'] . "," . "C:\\Builds\\" . $row['product'] . "\\" . $username . $extension . "," . "C:\\Builds\\" . $row['product'] . "," . $username . ",C:\\Builder\\" . $row['file_name']);
            }
            else
            {
                die($row['product'] . "," . "C:\\Builds\\" . $row['product'] . "\\" . $username . ".sys," . "C:\\Builds\\" . $row['product'] . "," . $username . ",C:\\Builder\\" . "Driver.sys");
            }
        }
    }
    else if ($_GET['action'] == "blacklist")
    {
        if (isset($_GET['hwid']))
        {
            $stmt = $conn->prepare("SELECT * FROM `blacklist` WHERE `hwid` = ?");
            $stmt->bind_param("s", $_GET['hwid']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == 0)
            {
                prt("success", $_GET['username'], $_GET['username']);
            }
            else
            {
                prt("blacklisted;" . $row['reason'], $_GET['username'], $_GET['username']);
            }
        }
    }
    else if ($_GET['action'] == "subscription")
    {
        if (isset($_GET['license']))
        {
            $stmt = $conn->prepare("SELECT * FROM `licenses` WHERE `license` = ?");
            $stmt->bind_param("s", $_GET['license']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == 0)
            {
                prt("invalid", $_GET['username'], $_GET['username']);
            }

            $server_expires_at_time;
            if ($row['expires_at'] != NULL)
            {
                $server_expires_at_time = new DateTime($row['expires_at']);
            }

            $current_time = date('d-m-Y H:i:s');
            $current_now_time = new DateTime($current_time);
            if ($row['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
            {
                prt("expired", $_GET['username'], $_GET['username']);
            }

            prt($row['product'], $_GET['username'], $_GET['username']);
        }
    }
    else if ($_GET['action'] == "productinfo")
    {
        if (isset($_GET['product']) && isset($_GET['username']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $createdAt = new DateTime($row['session_created_at']);
            $now = new DateTime();
            $interval = $now->diff($createdAt);

            if ($interval->i >= 10)
            {
                die("Session has expired. Please restart the loader.");
            }

            $encryption_key = $row['auth_encryption_hash'];

            if ($result->num_rows == NULL)
            {
                prt("Such account does not exist.", $encryption_key, $_GET['username']);
            }

            $stmt = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
            $stmt->bind_param("s", $_GET['product']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == 0)
            {
                prt("Error", $encryption_key, $_GET['username']);
            }

            if ($row['UseInjector'] != NULL)
            {
                if ($row['InjectorMethod'] == "usermode")
                {
                    prt($row['process'] . ";" . $row['window_name'], $encryption_key, $_GET['username']);
                }
            }
            else
            {
                prt($row['InjectorMethod'], $encryption_key, $_GET['username']);
            }
        }
    }
    else if ($_GET['action'] == "vfc")
    {
        if (isset($_GET['discord_id']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `discord_id` = ?");
            $stmt->bind_param("s", $_GET['discord_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == 0)
            {
                prt("invalid");
            }

            if ($row['2fa_enabled'] == 0)
            {
                prt("2fa is disabled. Please enable it on the user account dashboard.");
            }

            if ($row['2fa_code'] == 0)
            {
                $vfc_code = rndstring3(6);
                $stmt5 = $conn->prepare("UPDATE `user` SET `2fa_code` = ? WHERE `discord_id` = ?");
                $stmt5->bind_param("ss", $vfc_code, $_GET['discord_id']);
                $stmt5->execute();

                prt($vfc_code);
            }
            else
            {
                prt($row['2fa_code']);
            }
        }
    }
    else if ($_GET['action'] == "assign_discord")
    {
        if (isset($_GET['discord_id']) && isset($_GET['username']) && isset($_GET['password']))
        {
            $stmta = $conn->prepare("SELECT * FROM `user` WHERE `discord_id` = ?");
            $stmta->bind_param("s", $_GET['discord_id']);
            $stmta->execute();
            $resulta = $stmta->get_result();
            $rowa = $resulta->fetch_assoc();

            if ($resulta->num_rows > 0)
            {
                die("This discord account has already been asigned to a different loader account.");
            }


            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == NULL)
            {
                die("Such account does not exist.");
            }

            if ($row['password'] != $_GET['password'])
            {
                die("Password does not match with our records. If you forgot your password please contact an administrator!");
            }

            if ($row['discord_id'] != 0)
            {
                die("Account is already linked to: " . $row['discord_id']);
            }

            $stmt5 = $conn->prepare("UPDATE `user` SET `discord_id` = ? WHERE `username` = ?");
            $stmt5->bind_param("ss", $_GET['discord_id'], $_GET['username']);
            $stmt5->execute();

            die("Success");
        }
    }
    else if ($_GET['action'] == "connect_account")
    {
        if (isset($_GET['username']) && isset($_GET['password']) && isset($_GET['discord_id']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows == NULL)
            {
                die("Such account does not exist.");
            }

            if ($row['password'] == NULL)
            {
                $stmt5 = $conn->prepare("UPDATE `user` SET `password` = ? WHERE `username` = ?");
                $stmt5->bind_param("ss", $_GET['password'], $_GET['username']);
                $stmt5->execute();

                die("New password set, please login now.");
            }

            if ($row['password'] != $_GET['password'])
            {
                die("Password does not match with our records. If you forgot your password please contact an administrator!");
            }

            if ($row['ban_reason'] != NULL)
            {
                die("Account has been banned. Reason: " . $row['ban_reason']);
            }

            if ($row['discord_id_bot'] != NULL)
            {
                die("Account is already connected.");
            }

            //licenses hold on user account

            $stmt = $conn->prepare("SELECT * FROM `subscriptions` WHERE `user` = ?");
            $stmt->bind_param("s", $_GET['username']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc())
            {
                $current_time = date('d-m-Y H:i:s');
                $current_now_time = new DateTime($current_time);
                if ($row['expires_at'] != NULL)
                {
                    $server_expires_at_time = new DateTime($row['expires_at']);
                }
                if ($row['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
                {
                    //echo $row['subscription'] .",Expired,-,-;";
                }
                else
                {
                    $stmt89 = $conn->prepare("UPDATE `user` SET `discord_id_bot` = ? WHERE `username` = ?");
                    $stmt89->bind_param("ss", $_GET['discord_id'], $_GET['username']);
                    $stmt89->execute();

                    die("Success:" . $_GET['discord_id']);
                }
            }
        }
    }
    else if ($_GET['action'] == "check_account_dcid")
    {
        if (isset($_GET['discord_id']))
        {
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `discord_id_bot` = ?");
            $stmt->bind_param("s", $_GET['discord_id']);
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

            $username = $row['username'];
            //licenses hold on user account

            $stmt = $conn->prepare("SELECT * FROM `subscriptions` WHERE `user` = ?");
            $stmt->bind_param("s", $row['username']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc())
            {
                $current_time = date('d-m-Y H:i:s');
                $current_now_time = new DateTime($current_time);
                if ($row['expires_at'] != NULL)
                {
                    $server_expires_at_time = new DateTime($row['expires_at']);
                }
                if ($row['expires_at'] != NULL && $current_now_time >= $server_expires_at_time)
                {
                    //echo $row['subscription'] .",Expired,-,-;";
                }
                else
                {
                    die("Success:" . $_GET['discord_id']);
                }
            }
            $null = NULL;
            $stmt4566 = $conn->prepare("UPDATE `user` SET `discord_id_bot` = ? WHERE `username` = ?");
            $stmt4566->bind_param("ss", $null, $username);
            $stmt4566->execute();
            die("reset");
        }
    }
    else if ($_GET['action'] == "reset_hwid")
    {
        if (isset($_GET['username']) && isset($_GET['api']))
        {
            if ($_GET['api'] != "e4b7b6206ac87de1193b7ab960eb27a0a42591094edda8c362dc2705865c162b")
            {
                die("APIkey failure");
            }
            $stmt = $conn->prepare("SELECT * FROM `user` WHERE `username` = ?");
            $stmt->bind_param("s", $_GET['username']);
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
            $stmt4566->bind_param("ss", $null, $_GET['username']);
            $stmt4566->execute();
            die("Hardware-id has been reset.");
        }
    }
}
else
{
    die("API failure: Action type missing");
}
