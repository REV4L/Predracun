<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'a') {
    header("Location: prijava.php");
    exit();
}

echo "Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'];

// Dodaj novo kategorijo
if (isset($_POST['dodaj'])) {
    $ime = $_POST['ime'] ?? '';
    $opis = $_POST['opis'] ?? '';

    if ($ime !== '') {
        $stmt = $link->prepare("INSERT INTO kategorije (ime, opis) VALUES (?, ?)");
        $stmt->bind_param("ss", $ime, $opis);
        $stmt->execute();
        $stmt->close();
    }
}

// Briši kategorijo
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $link->prepare("DELETE FROM kategorije WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Posodobi kategorijo (prikaz obrazca)
$uredi_kategorijo = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $link->prepare("SELECT * FROM kategorije WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $uredi_kategorijo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Shrani posodobitev
if (isset($_POST['shrani'])) {
    $id = $_POST['id'];
    $ime = $_POST['ime'];
    $opis = $_POST['opis'];
    $stmt = $link->prepare("UPDATE kategorije SET ime = ?, opis = ? WHERE id = ?");
    $stmt->bind_param("ssi", $ime, $opis, $id);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="sl">

<head>
    <meta charset="UTF-8">
    <title>Upravljanje kategorij</title>
    <link rel="stylesheet" href="blagajna.css">
</head>

<body>
    <h1>Kategorije</h1>

    <!-- Obrazec za dodajanje ali urejanje -->
    <form method="post" action="">
        <h2><?php echo $uredi_kategorijo ? "Uredi kategorijo" : "Dodaj novo kategorijo"; ?></h2>
        <input type="hidden" name="id" value="<?php echo $uredi_kategorijo['id'] ?? ''; ?>">
        <label for="ime">Ime:</label><br>
        <input type="text" name="ime" required value="<?php echo htmlspecialchars($uredi_kategorijo['ime'] ?? ''); ?>"><br><br>
        <label for="opis">Opis:</label><br>
        <textarea name="opis" rows="4"><?php echo htmlspecialchars($uredi_kategorijo['opis'] ?? ''); ?></textarea><br><br>
        <button type="submit" name="<?php echo $uredi_kategorijo ? 'shrani' : 'dodaj'; ?>" class="btn akcija">
            <?php echo $uredi_kategorijo ? 'Shrani spremembe' : 'Dodaj kategorijo'; ?>
        </button>
    </form>

    <hr>

    <!-- Izpis vseh kategorij -->
    <?php
    $result = mysqli_query($link, "SELECT * FROM kategorije");
    echo "<table border='1' style='border-collapse: collapse'>";
    echo "<tr><th>ID</th><th>Ime</th><th>Opis</th><th>Akcija</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
        echo "<td>" . htmlspecialchars($row['opis']) . "</td>";
        echo "<td style='display: flex; gap: 8px;'>";

        echo "<a href='kategorije.php?edit=" . $row['id'] . "' class='btn akcija' style='background-color: #f39c12;'>Uredi</a>";
        echo "<a href='kategorije.php?delete=" . $row['id'] . "' class='btn akcija' style='background-color: #cc0000;' 
              onclick=\"return confirm('Res želite izbrisati to kategorijo?');\">Izbriši</a>";

        echo "</td></tr>";
    }
    echo "</table>";

    mysqli_close($link);
    ?>

    <br>
    <a href='admin.php' class='btn akcija'>Nazaj</a>
</body>

</html>