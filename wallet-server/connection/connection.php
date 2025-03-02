<?php

$db_host = "localhost";
$db_name = "wallet_db";
$db_user = "root";
$db_pass = "";

$mysqli = new mysqli($db_host, $db_name, $db_user, $db_pass);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

?>