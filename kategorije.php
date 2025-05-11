<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'a') {
    header("Location: prijava.php");
    exit();
}

echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "</div>";

// Dodajanje nove kategorije
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

// Brisanje kategorije
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $link->prepare("DELETE FROM kategorije WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Urejanje kategorije
$uredi_kategorijo = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $link->prepare("SELECT * FROM kategorije WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $uredi_kategorijo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Shrani urejanje
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding-top: 60px;
            background: url('https://media.istockphoto.com/id/1203327078/vector/abstract-background-of-smooth-curves.jpg?s=612x612&w=0&k=20&c=ybD3C-N3ZUX9abrp4XHrpSRhwubH0DiZ1-ZuZLWTBnY=') no-repeat center center fixed;
            background-size: cover;
        }

        .container {
            display: flex;
            flex-direction: row;
            gap: 20px;
            padding: 20px;
            align-items: flex-start;
        }

        .form-panel {
            flex: 1;
            max-width: 300px;
            background: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table-panel {
            flex: 3;
            overflow-x: auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .btn.akcija {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #0077cc;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn.akcija:hover {
            opacity: 0.85;
        }

        .btn-danger {
            background-color: #cc0000;
        }

        .btn-warning {
            background-color: #f39c12;
        }

        .pozdrav {
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2C9801;
            color: white;
            padding: 10px;
            width: 100%;
            text-align: center;
            z-index: 999;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Levi stolpec: Obrazec -->
        <div class="form-panel">
            <h2><?php echo $uredi_kategorijo ? "Uredi kategorijo" : "Dodaj novo kategorijo"; ?></h2>
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $uredi_kategorijo['id'] ?? ''; ?>">
                <label for="ime">Ime:</label><br>
                <input type="text" name="ime" required value="<?php echo htmlspecialchars($uredi_kategorijo['ime'] ?? ''); ?>"><br><br>
                <label for="opis">Opis:</label><br>
                <textarea name="opis" rows="4"><?php echo htmlspecialchars($uredi_kategorijo['opis'] ?? ''); ?></textarea><br><br>
                <button type="submit" name="<?php echo $uredi_kategorijo ? 'shrani' : 'dodaj'; ?>" class="btn akcija">
                    <?php echo $uredi_kategorijo ? 'Shrani spremembe' : 'Dodaj kategorijo'; ?>
                </button>
            </form>
            <br><br>
            <a href="admin.php" class="btn akcija">Nazaj</a>
        </div>

        <!-- Desni stolpec: Tabela -->
        <div class="table-panel">
            <h2>Seznam kategorij</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Ime</th>
                    <th>Opis</th>
                    <th>Akcija</th>
                </tr>
                <?php
                $result = mysqli_query($link, "SELECT * FROM kategorije");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['opis']) . "</td>";
                    echo "<td style='display: flex; gap: 8px;'>";

                    echo "<a href='kategorije.php?edit=" . $row['id'] . "' class='btn akcija btn-warning'>Uredi</a>";
                    echo "<a href='kategorije.php?delete=" . $row['id'] . "' class='btn akcija btn-danger'
                          onclick=\"return confirm('Res želite izbrisati to kategorijo?');\">Izbriši</a>";

                    echo "</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>