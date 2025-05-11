<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: prijava.php");
    exit();
}

if (!isset($_GET['artikel_id'])) {
    die("Nepravilen dostop.");
}

$id = intval($_GET['artikel_id']);
$force = isset($_GET['force']) && $_GET['force'] == '1';

if ($force) {
    // Izbriši vse povezave iz artikel_predracun
    $delStmt = $link->prepare("DELETE FROM artikel_predracun WHERE artikel_id = ?");
    $delStmt->bind_param("i", $id);
    $delStmt->execute();
    $delStmt->close();
}

$stmt = $link->prepare("DELETE FROM artikli WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>
        alert('Brisanje uspešno.');
        window.location.href = 'pregled_artiklov.php';
    </script>";
} else {
    echo "<script>
        alert('Brisanje neuspešno, izdelek je vsebovan v računu.');
        window.location.href = 'pregled_artiklov.php';
    </script>";
}

$stmt->close();
