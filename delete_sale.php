<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}

if (isset($_GET['sale_id'])) {
    $id = $_GET['sale_id'];
    $sql = mysqli_query($db,"DELETE FROM sales WHERE sale_id=$id");
    if ($sql) {
        header('location:sales.php');
    }
}
?>