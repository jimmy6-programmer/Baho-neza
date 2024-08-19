<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}

if (isset($_GET['ship_id'])) {
    $id = $_GET['ship_id'];
    $sql = mysqli_query($db,"DELETE FROM shipment WHERE ship_id=$id");
    if ($sql) {
        header('location:shipment.php');
    }
}
?>