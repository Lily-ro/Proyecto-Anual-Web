<?php
session_start();
if(!isset($_SESSION['rol'])){
    header("Location: ../index.php");
    exit;
}
