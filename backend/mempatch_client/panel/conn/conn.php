<?php
$conn = new mysqli("localhost", "root", "", "mempatch");
if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}

function rndstring1($n)
{
    $characters = '0123456789ACEBDF';

    $randomString = '';

    for ($i = 0; $i < $n; $i++) {

        $index = rand(0, strlen($characters) - 1);

        $randomString .= $characters[$index];

    }

    return $randomString;
}


function rndstringInvites()
{
    $randomString = rndstring1(32);

    return $randomString;
}

function rndstring()
{
    $randomString = rndstring1(8) . "-" . rndstring1(4) . "-" . rndstring1(4) . "-" . rndstring1(4) . "-" . rndstring1(12);

    return $randomString;
}

function rndstring_aes($n)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    $randomString = '';

    for ($i = 0; $i < $n; $i++) {

        $index = rand(0, strlen($characters) - 1);

        $randomString .= $characters[$index];

    }

    return $randomString;
}

function rsp_encrypt($plaintext, $key) {
    if (strlen($key) !== 32) {
        throw new Exception("Key must be 32 bytes for AES-256.");
    }

    // Generate a random IV
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Encrypt the plaintext
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($ciphertext === false) {
        throw new Exception("Encryption failed.");
    }

    // Encode IV and ciphertext to Base64
    $encoded_iv = base64_encode($iv);
    $encoded_ciphertext = base64_encode($ciphertext);

    // Return concatenated IV and ciphertext
    return $encoded_iv . ':' . $encoded_ciphertext;
}

function rsp_decrypt($encrypted_data, $key) {
    if (strlen($key) !== 32) {
        throw new Exception("Key must be 32 bytes for AES-256.");
    }

    // Split the encoded IV and ciphertext
    $parts = explode(':', $encrypted_data);
    if (count($parts) !== 2) {
        throw new Exception("Invalid encrypted data format.");
    }

    $encoded_iv = $parts[0];
    $encoded_ciphertext = $parts[1];

    // Decode IV and ciphertext from Base64
    $iv = base64_decode($encoded_iv);
    $ciphertext = base64_decode($encoded_ciphertext);

    // Decrypt the ciphertext
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($plaintext === false) {
        throw new Exception("Decryption failed.");
    }

    return $plaintext;
}
function log_loader($message, $username) 
{
    $file = 'C:\\loader_logs.txt';
    $timestamp = date('[Y-m-d H:i:s]');
    $logEntry = $timestamp . ' ' . $username . " -> " . $message . PHP_EOL;
    file_put_contents($file, $logEntry, FILE_APPEND | LOCK_EX);
}
function prt_static($input, $username= "null")
{
    log_loader($input, $username);

    $encrypted = rsp_encrypt($input, "NBx9qcCRiy00yR7TUStdheS2CUMqExPK");

    die(base64_encode($encrypted));
}

function prt($input, $key, $username = "null")
{
    log_loader($input, $username);
    //die($input);
    $encrypted = rsp_encrypt($input, $key);
    die(base64_encode($encrypted));
}

function serialize_xss($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

?>