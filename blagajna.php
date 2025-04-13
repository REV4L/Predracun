<?php
require_once 'baza.php';
session_start();

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

// Preveri, če je uporabnik admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'a';

// Izpis pozdrava
echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "<br>";
echo "<a href='odjava.php'>Odjava</a>";
if ($isAdmin) {
    echo " | <a href='admin.php'>Nazaj na admin panel</a>";
    echo " | <a href='pregled_predracunov_uporabnik.php'>Moji predračuni</a>";
}else{
    echo " | <a href='pregled_predracunov_uporabnik.php'>Moji predračuni</a>";
}
echo "</div>";
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Blagajna</title>
    <link rel="stylesheet" href="blagajna.css">
</head>
<body>
    <div class="container">
        <!-- Leva stran (predračun) -->
        <div class="left-panel">
            <div class="predracun">
                <h3>Izpis predračuna</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Artikel</th>
                            <th>Količina</th>
                            <th>Cena</th>
                            <th>Akcija</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (isset($_SESSION['racunId'])) {
                        $racunId = $_SESSION['racunId'];
                        $query = "SELECT r.id as rId, p.id, p.ime, p.cena FROM artikli p INNER JOIN artikli1 r ON p.id = r.artikel_id INNER JOIN predracun ra ON ra.id = r.predracun_id WHERE ra.id = $racunId";
                        $result = mysqli_query($link, $query);

                        $skupnaCena = 0;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $itemId = $row['id'];
                            $rId = $row['rId'];
                            $skupnaCena += $row['cena'];
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                            echo "<td>1</td>";
                            echo "<td>" . htmlspecialchars($row['cena']) . "€</td>";
                            echo "<td><form action='izbris_racun.php' method='post'><button type='submit' name='rId' value='$rId'>Izbris</button></form></td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <h4>Skupni znesek: <span id="skupni-znesek"><?php echo $skupnaCena; ?></span> €</h4>
            </div>

            <!-- Kategorije -->
            <div class="kategorije">
                <h3>Kategorije</h3>
                <form method="POST">
                    <?php
                    // Pridobi kategorije iz baze
                    $query = "SELECT * FROM kategorije";
                    $result = mysqli_query($link, $query);

                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                            $ime = htmlspecialchars($row['ime'], ENT_QUOTES, 'UTF-8');
                            echo "<button type='submit' name='kategorija_id' value='$id' class='kategorija-btn'>$ime</button>";
                        }
                    }
                    ?>
                </form>
            </div>
        </div>

        <!-- Desna stran (gumbi za nov račun in izdajo) -->
        <div class="right-panel">
            <form action="#" method="POST">
                <input type="submit" name="sub" value="novracun">
            </form>
            <form action="#" method="POST">
                <input type="submit" name="izdaja" value="izdaja_racuna">
            </form>

            <?php
            if (isset($_POST['sub']) && $_POST['sub'] == 'novracun') {
                $uporabnik_id = $_SESSION['uporabnik_id']; 
                $query = "INSERT INTO predracun (uporabnik_id, st, dt, izdan, skupna_cena, koncna_cena) VALUES ($uporabnik_id, 'NA', NOW(), 0, 0, 0)";
                $result = mysqli_query($link, $query);

                if ($result) {
                    $query = "SELECT id FROM predracun ORDER BY id DESC LIMIT 1";
                    $result = mysqli_query($link, $query);
                    $row = mysqli_fetch_assoc($result);
                    $_SESSION['racunId'] = $row['id'];
                } else {
                    echo "Napaka pri ustvarjanju novega računa: " . mysqli_error($link);
                }
            }

            if (isset($_POST['izdaja']) && $_POST['izdaja'] == 'izdaja_racuna') {
                unset($_SESSION['racunId']);
            }

            // Filtriranje izdelkov po kategoriji
            if (isset($_POST['kategorija_id'])) {
                $kategorija_id = $_POST['kategorija_id'];
                $query = "SELECT * FROM artikli WHERE kategorija_id = $kategorija_id";
                $result = mysqli_query($link, $query);

                if ($result) {
                    echo "<h4>Izdelki v kategoriji:</h4>";
                    echo "<table>";
                    echo "<tr><th>Ime</th><th>Cena</th><th>Akcija</th></tr>";
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['ime'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['cena'], ENT_QUOTES, 'UTF-8') . "€</td>";
                        echo "<td><button>Dodaj</button></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
