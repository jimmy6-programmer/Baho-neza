<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($db,"DELETE FROM milled_product WHERE mill_id=$id");
    if ($sql) {
        header('location:milled.php');
    }
}
?>