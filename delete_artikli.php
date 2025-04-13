<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: prijava.php");
    exit();
}

if (!isset($_POST['artikel_id'])) {
    die("Nepravilen dostop.");
}

$id = $_POST['artikel_id'];

$stmt = $link->prepare("DELETE FROM artikli WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: izpis_artiklov.php");
    exit();
} else {
    echo "Napaka pri brisanju: " . htmlspecialchars(mysqli_error($link));
}

$stmt->close();
$link->close();
?>
