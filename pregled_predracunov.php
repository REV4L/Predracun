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
    <title>Izpis predračunov</title>
    <link rel="stylesheet" href="izpis.css">
</head>
<body>
    <h1>Izpis predračunov</h1>

    <form method="post" action="">
        Uporabnik:
        <?php
        $query_users = "SELECT id, ime, priimek FROM uporabniki";
        $result_users = mysqli_query($link, $query_users);

        echo '<select name="uporabnik_id">';
        echo '<option value="">Vsi</option>';
        while ($user = mysqli_fetch_assoc($result_users)) {
            $selected = (isset($_POST['uporabnik_id']) && $_POST['uporabnik_id'] == $user['id']) ? 'selected' : '';
            echo "<option value=\"" . $user['id'] . "\" $selected>" . htmlspecialchars($user['ime']) . " " . htmlspecialchars($user['priimek']) . "</option>";
        }
        echo '</select>';
        ?>

        Datum od:
        <input type="date" name="datum_od" value="<?php echo isset($_POST['datum_od']) ? $_POST['datum_od'] : ''; ?>">

        do:
        <input type="date" name="datum_do" value="<?php echo isset($_POST['datum_do']) ? $_POST['datum_do'] : ''; ?>">

        <input type="submit" name="filter" value="Filtriraj">
    </form>

    <?php
    // Priprava osnovne poizvedbe in pogojev
    $query = "SELECT p.id, p.datum, p.izdan, p.skupna_cena, p.koncna_cena, u.ime, u.priimek
              FROM predracun p
              JOIN uporabniki u ON p.uporabnik_id = u.id";
    
    $conditions = [];
    $params = [];
    $types = "";

    if (!empty($_POST['uporabnik_id'])) {
        $conditions[] = "p.uporabnik_id = ?";
        $params[] = $_POST['uporabnik_id'];
        $types .= "i";
    }

    if (!empty($_POST['datum_od'])) {
        $conditions[] = "p.datum >= ?";
        $params[] = $_POST['datum_od'];
        $types .= "s";
    }

    if (!empty($_POST['datum_do'])) {
        $conditions[] = "p.datum <= ?";
        $params[] = $_POST['datum_do'];
        $types .= "s";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY p.datum DESC";

    // Izpiši poizvedbo za diagnostiko
    echo "<pre>$query</pre>";

    // Preverimo, če so parametri nastavljeni in jih povežemo
    if ($stmt = $link->prepare($query)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table border="1" style="border-collapse: collapse">';
        echo '<tr>
                <th>Št.</th>
                <th>Datum</th>
                <th>Izdan</th>
                <th>Skupna cena</th>
                <th>Končna cena</th>
                <th>Uporabnik</th>
              </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            $izdan = $row['izdan'] ? 'Da' : 'Ne';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['datum']) . "</td>";
            echo "<td>" . $izdan . "</td>";
            echo "<td>" . htmlspecialchars($row['skupna_cena']) . " €</td>";
            echo "<td>" . htmlspecialchars($row['koncna_cena']) . " €</td>";
            echo "<td>" . htmlspecialchars($row['ime']) . " " . htmlspecialchars($row['priimek']) . "</td>";
            echo "</tr>";
        }

        echo '</table>';
        $stmt->close();
    } else {
        echo "Napaka pri pripravi poizvedbe. Prosim preverite SQL poizvedbo.";
    }

    mysqli_close($link);
    ?>

    <br>
    <a href='admin.php'>Nazaj</a>
</body>
</html>
