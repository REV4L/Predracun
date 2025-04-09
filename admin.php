<?php
require_once 'baza.php';
require_once 'seja.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panela</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="topnav">
  <a href='blagajna.php'>BLAGAJNA</a>
  <a href="odjava.php">ODJAVA</a>
</div>  
<div class="container">
    <?php
    echo "<span>Pozdravljeni! Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek']."</span>";
    ?>
<ul>
    <li><a href='registracija.php'>Registracija novega uporabnika</a></li>
    <li><a href='izpis_uporabnikov.php'>Pregled zaposlenih</a></li>
    <li><a href='pregled_artiklov.php'>Pregled artiklov</a></li>
    <li><a href='dodaj_artikle.php'>Dodajanje artiklov</a></li>
    <li><a href='pregled_predracunov.php'>Pregled predračunov</a></li>
</ul>
</div>
</body>
</html>