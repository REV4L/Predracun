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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <div class="admin-container">
        <h2>Admin meni</h2>
        <?php
        echo "<p class='pozdrav'>Pozdravljeni! Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "</p>";
        ?>
        <div class="menu-buttons">
            <a href="blagajna.php"><i class="fas fa-cash-register"></i> Blagajna</a>
            <a href="registracija.php"><i class="fas fa-user-plus"></i> Registracija</a>
            <a href="izpis_uporabnikov.php"><i class="fas fa-users"></i> Pregled zaposlenih</a>
            <a href="pregled_artiklov.php"><i class="fas fa-boxes"></i> Pregled artiklov</a>
            <a href="dodaj_artikle.php"><i class="fas fa-plus-circle"></i> Dodaj artikle</a>
            <a href="pregled_predracunov.php"><i class="fas fa-file-invoice"></i> Predračuni</a>
            <a href="kategorije.php"><i class="fas fa-boxes"></i> Kategorije</a>
            <a href="odjava.php" class="logout"><i class="fas fa-sign-out-alt"></i> Odjava</a>
        </div>
    </div>
</body>

</html>