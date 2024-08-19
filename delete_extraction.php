<?php
session_start();
include "config.php";
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}

if (isset($_GET['ext_id'])) {
    $id = $_GET['ext_id'];
    $sql = mysqli_query($db,"DELETE FROM extraction WHERE ext_id=$id");
    if ($sql) {
        header('location:extraction.php');
    }
}
?>