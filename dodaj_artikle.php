<?php
require_once 'baza.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'a') {
    header("Location: prijava.php");
    exit();
}

$uspesno = false;
$napaka = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sub'])) {
    $ime = $_POST['ime'];
    $cena = $_POST['cena'];
    $kolicina = $_POST['kolicina'];
    $kategorija_id = $_POST['kategorija_id'];
    $opis = !empty($_POST['opis']) ? $_POST['opis'] : NULL;

    if (!empty($ime) && !empty($cena) && !empty($kolicina) && !empty($kategorija_id)) {
        $stmt = $link->prepare("INSERT INTO artikli (ime, cena, kolicina, kategorija_id, opis) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiss", $ime, $cena, $kolicina, $kategorija_id, $opis);

        if ($stmt->execute()) {
            $uspesno = true;
        } else {
            $napaka = "Napaka pri vnosu: " . $link->error;
        }

        $stmt->close();
    } else {
        $napaka = "Vsa obvezna polja morajo biti izpolnjena!";
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Dodajanje artiklov</title>
    <link rel="stylesheet" href="prijava.css">
    <style>
        .obvestilo {
            text-align: center;
            margin: 20px auto;
            padding: 15px 20px;
            width: fit-content;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Dodajanje artiklov</h1>

        <?php if ($uspesno): ?>
            <div class="obvestilo success">Artikel uspešno dodan.</div>
            <script>
                setTimeout(() => { window.location.href = 'admin.php'; }, 3000);
            </script>
        <?php elseif (!empty($napaka)): ?>
            <div class="obvestilo error"><?php echo $napaka; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <span>Ime: </span><input type="text" name="ime" placeholder="Ime" required> <br>
            <span>Cena: </span><input type="number" step="0.01" name="cena" placeholder="Cena" required> <br>
            <span>Količina: </span><input type="number" name="kolicina" placeholder="Količina" required min="1" step="1"> <br>
            <span>Opis: </span><textarea name="opis" placeholder="Opis (neobvezno)" rows="4" cols="50"></textarea> <br>

            <span>Kategorija: </span>
            <select name="kategorija_id" required>
                <?php
                $query = "SELECT id, ime FROM kategorije";
                $result = mysqli_query($link, $query);

                if (!$result) {
                    echo '<option disabled>Napaka pri nalaganju kategorij</option>';
                } else {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                        $ime = htmlspecialchars($row['ime'], ENT_QUOTES, 'UTF-8');
                        echo "<option value=\"$id\">$ime</option>";
                    }
                }
                ?>
            </select>
            <br>
            <div id="center">
                <input type="submit" name="sub" value="Vnos" id="submit">
            </div>
        </form>

        <a href="admin.php" id="link">Nazaj</a><br>
    </div>
</body>
</html>
