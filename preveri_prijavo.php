<?php
require_once 'baza.php';
session_start();
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 'On');
$e = $_POST['user'];
$g = $_POST['pas'];
$gkod = sha1($g);

$sql2 = "SELECT * FROM uporabniki WHERE email ='$e' AND pasw='$gkod';";
$result2 = mysqli_query($link, $sql2);
$st = mysqli_num_rows($result2);

if ($st > 0) {
    $row = mysqli_fetch_array($result2);
    $_SESSION['ime'] = $row['ime'];
    $_SESSION['priimek'] = $row['priimek'];
    $_SESSION['uporabnik_id'] = $row['id'];
    $_SESSION['role'] = $row['role'];
    if ($_SESSION['role'] == 'a') {
        header("Location: https://predracun.nikola-marinkovic.eu/admin.php");
        exit();
    } else {
        header("Location: https://predracun.nikola-marinkovic.eu/blagajna.php");
        exit();
    }
} else {
    echo "Uporabniško ime ali geslo ni pravilno.";
    header("Refresh:5; url=index.php");
    exit();
}
?>