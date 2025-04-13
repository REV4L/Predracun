<?php
require_once 'baza.php';
session_start();

if (isset($_SESSION['racunId'], $_POST['artikel_id'])) {
    $racunId = $_SESSION['racunId'];
    $artikel_id = intval($_POST['artikel_id']);

    $check = mysqli_query($link, "SELECT id, kolicina FROM artikli1 WHERE predracun_id = $racunId AND artikel_id = $artikel_id");
    if ($row = mysqli_fetch_assoc($check)) {
        $nova_kolicina = $row['kolicina'] + 1;
        mysqli_query($link, "UPDATE artikel_predracun SET kolicina = $nova_kolicina WHERE id = {$row['id']}");
    } else {
        mysqli_query($link, "INSERT INTO artikel_predracun (predracun_id, artikel_id, kolicina) VALUES ($racunId, $artikel_id, 1)");
    }
}

header("Location: blagajna.php");
exit();
