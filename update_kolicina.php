<?php
require_once 'baza.php';
session_start();

if (isset($_POST['rId'], $_POST['kolicina'])) {
    $rId = intval($_POST['rId']);
    $kolicina = max(1, intval($_POST['kolicina']));
    $query = "UPDATE artikel_predracun SET kolicina = $kolicina WHERE id = $rId";
    mysqli_query($link, $query);
}

header("Location: blagajna.php");
exit();
