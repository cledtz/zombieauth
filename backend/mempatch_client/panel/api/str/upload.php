<?php
include '../../conn/conn.php';
session_start();
$message = '';

function delete_folder($folderPath) {
    // Check if the folder exists
    if (!is_dir($folderPath)) {
        //echo "The folder does not exist.\n";
        return false;
    }

    // Open the folder
    $files = array_diff(scandir($folderPath), array('.', '..'));

    // Loop through the files and delete them
    foreach ($files as $file) {
        $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;

        // Check if it's a file or a folder
        if (is_dir($filePath)) {
            // Recursively delete subfolder
            deleteFolder($filePath);
        } else {
            // Delete the file
            unlink($filePath);
        }
    }

    // Remove the folder itself
    if (rmdir($folderPath)) {
        //echo "Folder and all its contents have been deleted.\n";
        return true;
    } else {
        //echo "Failed to delete the folder.\n";
        return false;
    }
}

if (isset($_POST['uploadBtn']) && $_POST['uploadBtn'] == 'Upload') 
{
    if ($_POST['pwd'] == "dev1337!") 
    {
        if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) 
        {
            // get details of the uploaded file
            $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
            $fileName = $_FILES['uploadedFile']['name'];
            $fileSize = $_FILES['uploadedFile']['size'];
            $fileType = $_FILES['uploadedFile']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            if ($_POST['proid'] == "Injector" || $_POST['proid'] == "Client" || $_POST['proid'] == "Driver") 
            {
                $newFileName;

                if ($_POST['proid'] == "Injector")
                {
                    $newFileName = "Injector.dll";
                }
                else if ($_POST['proid'] == "Client")
                {
                    $newFileName = "Client.dll";
                }
                else if ($_POST['proid'] == "Driver")
                {
                    $newFileName = "C:\\Builder\\Driver.sys";
                }

                // check if file has one of the following extensions
                $allowedfileExtensions = array('dll', 'exe', 'sys', 'bin');

                if (in_array($fileExtension, $allowedfileExtensions)) 
                {
                    // directory in which the uploaded file will be moved
                    //$uploadFileDir = './';
                    //$dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $newFileName)) 
                    {
                        $method = 'aes-256-cfb';
                        $filesize = filesize($newFileName);
                        // open file for reading in binary mode
                        $fp = fopen($newFileName, 'rb');
                        // read the entire file into a binary string
                        $binary = fread($fp, $filesize);
                        // finally close the file
                        fclose($fp);


                        //$gay = base64_encode($binary);

                        //$returngood = base64_encode(openssl_encrypt($gay, $method, "98G2Lu7vyf62lvM6xE85CvE8w3ws8a37", true, "63gNJ8BbdDoX43sJ"));

                        $fp1 = fopen($newFileName, "wb");

                        // Write the byte to the file
                        fwrite($fp1, $binary);

                        fclose($fp1);

                        $path_folder = "C:\\Builds\\" .$_POST['proid'];
                        delete_folder($path_folder);

                        $message = 'Success: File has been uploaded to server.';

                    } 
                    else 
                    {
                        $message = 'Failure: There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                    }
                } 
                else 
                {
                    $message = 'Failure: Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
                }
            } 
            else
            {
                $stmta = $conn->prepare("SELECT * FROM `products` WHERE `product` = ?");
                $stmta->bind_param("s", $_POST['proid']);
                $stmta->execute();
                $resulta = $stmta->get_result();
                $rowa = $resulta->fetch_assoc();
                if ($resulta->num_rows == 0) {
                    $_SESSION['message'] = 'Failure: Please enter a valid Product-ID!';
                    header("Location: index.php");
                    return;
                }

                $newFileName = "C:\\Builder\\" . $rowa['file_name'];

                // check if file has one of the following extensions
                $allowedfileExtensions = array('dll', 'exe', 'sys');

                if (in_array($fileExtension, $allowedfileExtensions)) 
                {
                    if (move_uploaded_file($fileTmpPath, $newFileName)) 
                    {
                        $method = 'aes-256-cfb';
                        $filesize = filesize($newFileName);
                        // open file for reading in binary mode
                        $fp = fopen($newFileName, 'rb');
                        // read the entire file into a binary string
                        $binary = fread($fp, $filesize);
                        // finally close the file
                        fclose($fp);


                        //$gay = base64_encode($binary);

                        //$returngood = base64_encode(openssl_encrypt($gay, $method, "98G2Lu7vyf62lvM6xE85CvE8w3ws8a37", true, "63gNJ8BbdDoX43sJ"));

                        $fp1 = fopen($newFileName, "wb");

                        // Write the byte to the file
                        //fwrite($fp1, $returngood);
                        fwrite($fp1, $binary);

                        fclose($fp1);

                        $current_time = date('d-m-Y H:i:s');
                        $stmt2 = $conn->prepare("UPDATE `products` SET `last_upload` = ? WHERE `product` = ?");
                        $stmt2->bind_param("ss", $current_time, $_POST['proid']);
                        $stmt2->execute();

                        $path_folder = "C:\\" . $rowa['product'];
                        delete_folder($path_folder);

                        $message = 'Success: '. $_POST['proid'] . ' has been uploaded to server. (Upload date: ' . $current_time . ')';
                    } 
                    else 
                    {
                        $message = 'Failure: There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                    }
                } 
                else 
                {
                    $message = 'Failure: Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
                }
            }
        }
        else 
        {
            $message = 'Failure: There is some error in the file upload. Please check the following error.<br>';
            $message .= 'Error:' . $_FILES['uploadedFile']['error'];
        }
    }
    else 
    {
        $message = 'Failure: Incorrect Developer-ID!';
    }

    $_SESSION['message'] = $message;
    header("Location: index.php");
}

?>