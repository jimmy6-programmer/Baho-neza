<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}

if (isset($_GET['final_pro_id'])) {
    $id = $_GET['final_pro_id'];
    $sql = mysqli_query($db,"DELETE FROM final_product WHERE final_pro_id=$id");
    if ($sql) {
        header('location:final_product.php');
    }
}
?>