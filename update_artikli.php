<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: prijava.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Nepravilen dostop.");
}

$id = $_GET['id'];
$uspesno = false;
$napaka = '';

$stmt = $link->prepare("SELECT * FROM artikli WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$artikel = $result->fetch_assoc();

if (!$artikel) {
    die("Artikel ne obstaja.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $cena = $_POST['cena'];
    $kolicina = $_POST['kolicina'];
    $opis = $_POST['opis'];
    $kategorija_id = $_POST['kategorija_id'];

    if (empty($ime) || empty($cena) || empty($kolicina) || empty($kategorija_id)) {
        $napaka = "Vsa obvezna polja morajo biti izpolnjena.";
    } else {
        $stmt = $link->prepare("UPDATE artikli SET ime = ?, cena = ?, kolicina = ?, opis = ?, kategorija_id = ? WHERE id = ?");
        $stmt->bind_param("sdissi", $ime, $cena, $kolicina, $opis, $kategorija_id, $id);

        if ($stmt->execute()) {
            $uspesno = true;
        } else {
            $napaka = "Napaka pri posodabljanju: " . htmlspecialchars(mysqli_error($link));
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Uredi artikel</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
<div class="form-container">
    <h1>Uredi artikel</h1>

    <?php if ($uspesno): ?>
        <div class="obvestilo success">Artikel uspešno posodobljen.</div>
        <script>setTimeout(() => window.location.href = 'pregled_artiklov.php', 2000);</script>
    <?php endif; ?>

    <?php if (!empty($napaka)): ?>
        <div class="obvestilo error"><?php echo $napaka; ?></div>
    <?php endif; ?>

    <form method="post">
        <span>Ime:</span>
        <input type="text" name="ime" value="<?php echo htmlspecialchars($artikel['ime']); ?>" required><br>
        <span>Cena:</span>
        <input type="text" name="cena" value="<?php echo htmlspecialchars($artikel['cena']); ?>" required><br>
        <span>Količina:</span>
        <input type="number" name="kolicina" value="<?php echo htmlspecialchars($artikel['kolicina']); ?>" required><br>
        <span>Opis:</span>
        <textarea name="opis"><?php echo htmlspecialchars($artikel['opis']); ?></textarea><br>
        <span>Kategorija:</span>
        <select name="kategorija_id" required>
            <?php
            $result = $link->query("SELECT id, ime FROM kategorije");
            while ($row = $result->fetch_assoc()) {
                $selected = $row['id'] == $artikel['kategorija_id'] ? "selected" : "";
                echo "<option value=\"{$row['id']}\" $selected>" . htmlspecialchars($row['ime']) . "</option>";
            }
            ?>
        </select><br>
        <div id="center">
            <input type="submit" value="Posodobi" id="submit">
        </div>
    </form>
    <a href="izpis_artiklov.php" id="link">Nazaj</a>
</div>
</body>
</html>
<?php $link->close(); ?>
