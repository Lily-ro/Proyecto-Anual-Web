<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "u156482620_EVAelvigilante";
$pass = "#VALzona122233";
$db   = "u156482620_EVAelvigilante";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("ERROR MYSQL: " . $conn->connect_error);
}