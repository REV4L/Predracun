<?php
require_once 'baza.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'a') {
    header("Location: prijava.php");
    exit();
}

if (isset($_GET["prefix"])) {
    $p = $_GET["prefix"];
    $query = "UPDATE settings SET prefix = ? WHERE 1=1";

    $link->prepare($query)->execute();


    header("Location: pregled_predracunov.php");
}