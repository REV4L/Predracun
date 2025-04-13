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
<div class="sidebar">
    <h2>Admin meni</h2>
    <a href="blagajna.php"><i class="fas fa-cash-register"></i> Blagajna</a>
    <a href="registracija.php"><i class="fas fa-user-plus"></i> Registracija</a>
    <a href="izpis_uporabnikov.php"><i class="fas fa-users"></i> Pregled zaposlenih</a>
    <a href="pregled_artiklov.php"><i class="fas fa-boxes"></i> Pregled artiklov</a>
    <a href="dodaj_artikle.php"><i class="fas fa-plus-circle"></i> Dodaj artikle</a>
    <a href="pregled_predracunov.php"><i class="fas fa-file-invoice"></i> Predračuni</a>
    <a href="odjava.php" class="logout"><i class="fas fa-sign-out-alt"></i> Odjava</a>

    <div class="toggle-theme">
        <button onclick="toggleTheme()">🌓 Zamenjaj temo</button>
    </div>
</div>

<div class="container">
    <?php
    echo "<span>Pozdravljeni! Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "</span>";
    ?>
</div>

<script>
function toggleTheme() {
    document.body.classList.toggle("dark-mode");
}
</script>
</body>
</html>