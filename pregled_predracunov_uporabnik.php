<?php
require_once 'baza.php';
session_start();

// Dovoli samo prijavljenim uporabnikom (ni admin preverjanja)
if (!isset($_SESSION['ime'])) {
    header("Location: prijava.php");
    exit();
}

echo "Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'];
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Moji predračuni</title>
    <link rel="stylesheet" href="izpis.css">
</head>
<body>
    <h1>Moji predračuni</h1>

    <form method="post" action="">
        Datum od:
        <input type="date" name="datum_od" value="<?php echo isset($_POST['datum_od']) ? $_POST['datum_od'] : ''; ?>">

        do:
        <input type="date" name="datum_do" value="<?php echo isset($_POST['datum_do']) ? $_POST['datum_do'] : ''; ?>">

        <input type="submit" name="filter" value="Filtriraj">
    </form>

    <?php
    $query = "SELECT p.st, p.dt, p.izdan, p.skupna_cena, p.koncna_cena
    FROM predracun p
    WHERE p.uporabnik_id = (
        SELECT id FROM uporabniki WHERE ime = ? AND priimek = ?
    )";


    $params = [$_SESSION['id']];
    $types = "i";

    if (!empty($_POST['datum_od'])) {
        $query .= " AND p.dt >= ?";
        $params[] = $_POST['datum_od'];
        $types .= "s";
    }

    if (!empty($_POST['datum_do'])) {
        $query .= " AND p.dt <= ?";
        $params[] = $_POST['datum_do'];
        $types .= "s";
    }

    $query .= " ORDER BY p.dt DESC";

    if ($stmt = $link->prepare($query)) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table border="1" style="border-collapse: collapse">';
        echo '<tr>
                <th>Št.</th>
                <th>Datum</th>
                <th>Izdan</th>
                <th>Skupna cena</th>
                <th>Končna cena</th>
              </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            $izdan = $row['izdan'] ? 'Da' : 'Ne';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['st']) . "</td>";
            echo "<td>" . htmlspecialchars($row['dt']) . "</td>";
            echo "<td>" . $izdan . "</td>";
            echo "<td>" . htmlspecialchars($row['skupna_cena']) . " €</td>";
            echo "<td>" . htmlspecialchars($row['koncna_cena']) . " €</td>";
            echo "</tr>";
        }

        echo '</table>';
        $stmt->close();
    } else {
        echo "Napaka pri pripravi poizvedbe.";
    }

    mysqli_close($link);
    ?>

    <br>
    <a href='blagajna.php'>Nazaj</a>
</body>
</html>
