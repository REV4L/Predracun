<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'a') {
    header("Location: prijava.php");
    exit();
}

echo "Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'];
?>
<!DOCTYPE html>
<html lang="sl">

<head>
    <meta charset="UTF-8">
    <title>Izpis artiklov</title>
    <link rel="stylesheet" href="izpis.css">
</head>

<body>
    <h1>Izpis artiklov</h1>
    <form method="post" action="">
        Kategorija:
        <?php
        $query = "SELECT id, ime FROM kategorije";
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
        <input type="submit" name="submit" value="Prikaži">
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $kategorija_id = $_POST['kategorija_id'];

        $query = "SELECT a.id, a.ime, a.cena, a.kolicina, a.opis, a.kategorija_id, k.ime AS kategorija_ime 
                  FROM artikli a
                  JOIN kategorije k ON a.kategorija_id = k.id
                  WHERE a.kategorija_id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $kategorija_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table border="1" style="border-collapse: collapse">';
        echo '<tr><th>Ime</th><th>Cena</th><th>Količina</th><th>Opis</th><th>Kategorija</th><th>Akcija</th></tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cena']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kolicina']) . "</td>";
            echo "<td>" . htmlspecialchars($row['opis']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kategorija_ime']) . "</td>";
            echo "<td>
            <form action='delete_artikli.php' method='POST' style='display:inline;'>
                <input type='hidden' name='artikel_id' value='" . $row['id'] . "'>
                <button type='submit'>Izbriši</button>
            </form>
            <a href='delete_artikli.php?artikel_id=" . $row['id'] . "&force=1' 
               onclick=\"return confirm('Res želite trajno izbrisati artikel, tudi če je že uporabljen?');\" 
               style='margin-left: 10px; color: red; text-decoration: underline;'>
               Force Delete
            </a>
            <form action='update_artikli.php' method='GET' style='display:inline; margin-left: 10px;'>
                <input type='hidden' name='id' value='" . $row['id'] . "'>
                <button type='submit'>Posodobi</button>
            </form>
          </td>";
            echo "</tr>";
        }
        echo "</table>";


        $stmt->close();
    }

    mysqli_close($link);
    ?>

    <br>
    <a href='admin.php'>Nazaj</a>
</body>

</html>