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
    <title>Izpis uporabnikov</title>
    <link rel="stylesheet" href="izpis.css">
</head>
<body>
    <h1>Izpis uporabnikov</h1>
    <?php
    $query = 'SELECT * FROM uporabniki ORDER BY id';
    $result = mysqli_query($link, $query);

    echo '<table border="1" style="border-collapse: collapse">';
    echo '<tr><th>ID</th><th>Ime</th><th>Priimek</th><th>Telefonska</th><th>Uporabniško ime</th><th>Akcija</th></tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
        echo "<td>" . htmlspecialchars($row['priimek']) . "</td>";
        echo "<td>" . htmlspecialchars($row['telefon']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        if ($row['role'] != 'a') {
            echo "<td>
                    <form action='delete_user.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                        <button type='submit'>Izbriši</button>
                    </form>
                    <form action='update_uporabnike.php' method='GET' style='display:inline;'>
                        <input type='hidden' name='id' value='" . $row['id'] . "'>
                        <button type='submit'>Posodobi</button>
                    </form>
                </td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    mysqli_close($link);
    ?>
    <br>
    <a href='admin.php'>Nazaj</a>
</body>
</html>