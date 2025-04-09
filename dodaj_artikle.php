<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'a') {
    header("Location: prijava.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Dodajanje artiklov</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
    <div class="form-container">
        <h1>Dodajanje artiklov</h1>
        <form action="add_artikle.php" method="post">
            <span>Ime: </span><input type="text" name="ime" placeholder="Ime" required> <br>
            <span>Cena: </span><input type="text" name="cena" placeholder="Cena" required> <br>
            <span>Količina: </span><input type="number" name="kolicina" placeholder="Količina" required min="1" step="1"> <br>
            <span>Opis: </span><textarea name="opis" placeholder="Opis (neobvezno)" rows="4" cols="50"></textarea> <br>

            <span>Kategorija: </span>
            <?php
            $query = "SELECT id, ime FROM kategorije"; // Pridobivanje kategorij iz tabele 'kategorije'
            $result = mysqli_query($link, $query);

            if (!$result) {
                die("Napaka pri pridobivanju kategorij: " . mysqli_error($link));
            }

            echo '<select name="kategorija_id" required>';
            while ($row = mysqli_fetch_assoc($result)) {
                $id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                $ime = htmlspecialchars($row['ime'], ENT_QUOTES, 'UTF-8');
                echo "<option value=\"$id\">$ime</option>";
            }
            echo '</select>';
            ?>
            <br>
            <div id="center">
                <input type="submit" name="sub" value="Vnos" id="submit">
            </div>
        </form>
        <a href="admin.php" id="link">Nazaj</a><br>
    </div>
</body>
</html>
