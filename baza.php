<?php
$host = "nikola-marinkovic.eu";
$user = "nikola26";
$password = "Nikola57ma!";
$db = "nikola26_Artikli";

$link = new mysqli($host, $user, $password, $db);
$link->set_charset("utf8mb4");

if ($link->connect_error) {
    die("Povezava na bazo ni uspela: " . $link->connect_error);
}

mysqli_set_charset($link, "utf8");
