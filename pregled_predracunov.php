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

    <form method="get" action="setprefix.php">
        <?php
        $qprefix = "SELECT prefix FROM settings LIMIT 1";
        $result = mysqli_query($link, $qprefix);
        $row = mysqli_fetch_assoc($result);
        $prefix = $row['prefix'];

        echo '<input name="prefix" type="text" value="' . htmlspecialchars($prefix) . '">';
        ?>
        <input name="sub" type="submit" value="Shrani">
    </form>

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
    $query = "SELECT p.id, p.st, p.dt, p.izdan, p.skupna_cena, p.koncna_cena, 
                     u.ime AS uporabnik_ime, u.priimek,
                     p.ime_kupca, p.priimek_kupca
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
        $conditions[] = "p.dt >= ?";
        $params[] = $_POST['datum_od'];
        $types .= "s";
    }

    if (!empty($_POST['datum_do'])) {
        $conditions[] = "p.dt <= ?";
        $params[] = $_POST['datum_do'];
        $types .= "s";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY p.dt DESC";

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
                <th>Prodajalec</th>
                <th>Kupec</th>
                <th>Uredi</th>
              </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            $izdan = $row['izdan'] ? 'Da' : 'Ne';
            $prodajalec = htmlspecialchars($row['uporabnik_ime']) . " " . htmlspecialchars($row['priimek']);
            $kupec = htmlspecialchars($row['ime_kupca']) . " " . htmlspecialchars($row['priimek_kupca']);

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['st']) . "</td>";
            echo "<td>" . htmlspecialchars($row['dt']) . "</td>";
            echo "<td>" . $izdan . "</td>";
            echo "<td>" . htmlspecialchars($row['skupna_cena']) . " €</td>";
            echo "<td>" . htmlspecialchars($row['koncna_cena']) . " €</td>";
            echo "<td>" . $prodajalec . "</td>";
            echo "<td>" . $kupec . "</td>";
            echo '<td><a href="blagajna.php?edit=' . $row['id'] . '" style="background-color: #d0e7ff; padding: 5px 10px; text-decoration: none; color: black; border-radius: 4px; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'#a3d2ff\'" onmouseout="this.style.backgroundColor=\'#d0e7ff\'">Uredi</a></td>';
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