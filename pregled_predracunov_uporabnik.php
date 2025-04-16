<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

echo "Prijavljeni ste kot " . htmlspecialchars($_SESSION['ime']) . " " . htmlspecialchars($_SESSION['priimek']);
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
    $query = "SELECT p.st, p.dt, p.izdan, p.skupna_cena, p.koncna_cena, 
                     u.ime AS prodajalec_ime, u.priimek AS prodajalec_priimek,
                     p.ime_kupca, p.priimek_kupca
              FROM predracun p
              JOIN uporabniki u ON p.uporabnik_id = u.id
              WHERE u.ime = ? AND u.priimek = ?";

    $params = [$_SESSION['ime'], $_SESSION['priimek']];
    $types = "ss";

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
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table border="1" style="border-collapse: collapse">';
        echo '<tr>
                <th>Št.</th>
                <th>Datum</th>
                <th>Izdan</th>
                <th>Skupna cena</th>
                <th>Končna cena</th>
                <th>Prodajalec</th>
                <th>Kupec</th>
              </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            $izdan = $row['izdan'] ? 'Da' : 'Ne';
            $prodajalec = htmlspecialchars($row['prodajalec_ime']) . " " . htmlspecialchars($row['prodajalec_priimek']);
            $kupec = htmlspecialchars($row['ime_kupca']) . " " . htmlspecialchars($row['priimek_kupca']);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['st']) . "</td>";
            echo "<td>" . htmlspecialchars($row['dt']) . "</td>";
            echo "<td>" . $izdan . "</td>";
            echo "<td>" . htmlspecialchars($row['skupna_cena']) . " €</td>";
            echo "<td>" . htmlspecialchars($row['koncna_cena']) . " €</td>";
            echo "<td>" . $prodajalec . "</td>";
            echo "<td>" . $kupec . "</td>";
            echo "</tr>";
        }

        echo '</table>';
        $stmt->close();
    } else {
        echo "Napaka pri pripravi poizvedbe: " . $link->error;
    }

    mysqli_close($link);
    ?>

    <br>
    <a href='blagajna.php'>Nazaj</a>
</body>

</html>