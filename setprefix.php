<?php
require_once 'baza.php';



if (isset($_GET["prefix"])) {
    $p = $_GET["prefix"];
    $query = "UPDATE settings SET prefix = ? WHERE 1=1";

    $link->prepare($query)->execute();

    sleep(1);
    header("Location: pregled_predracunov.php");
}