<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}

if (isset($_GET['raw_id'])) {
    $id = $_GET['raw_id'];
    $sql = mysqli_query($db,"DELETE FROM raw_material WHERE raw_id=$id");
    if ($sql) {
        header('location:raw_material.php');
    }
}
?>