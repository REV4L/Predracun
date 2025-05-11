<?php
require_once 'baza.php';
session_start();


error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

if (isset($_GET["edit"])) {
    $_SESSION['racunId'] = $_GET["edit"];
}


if (isset($_POST['izdaja']) || isset($_POST['shrani'])) {
    $racunId = $_SESSION['racunId'];

    $query = "SELECT a.cena, r.kolicina FROM artikli a 
              INNER JOIN artikel_predracun r ON a.id = r.artikel_id 
              WHERE r.predracun_id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $racunId);
    $stmt->execute();
    $result = $stmt->get_result();

    $skupnaCena = 0;
    while ($row = $result->fetch_assoc()) {
        $skupnaCena += $row['cena'] * $row['kolicina'];
    }
    $stmt->close();

    $popust = isset($_POST['popust']) ? floatval($_POST['popust']) : 0;
    $koncnaCena = $skupnaCena - ($skupnaCena * $popust / 100);

    $izdan = isset($_POST['izdaja']) ? 1 : 0;

    $stmt = $link->prepare("UPDATE predracun SET izdan = $izdan, skupna_cena = ?, koncna_cena = ? WHERE id = ?");
    $stmt->bind_param("ddi", $skupnaCena, $koncnaCena, $racunId);
    $stmt->execute();
    $stmt->close();

    echo "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'a';


if (isset($_POST['sub']) && $_POST['sub'] == 'novracun') {
    $uporabnik_id = $_SESSION['uporabnik_id'];
    $ime_kupca = $_POST['ime_kupca'] ?? '';
    $priimek_kupca = $_POST['priimek_kupca'] ?? '';

    $stmt = $link->prepare("SELECT prefix FROM settings LIMIT 1");
    $stmt->execute();
    $resprefix = $stmt->get_result();
    $prefixRow = $resprefix->fetch_assoc();
    $prefix = $prefixRow['prefix'];
    $stmt->close();

    $prefixLength = strlen($prefix);
    $stmt = $link->prepare("SELECT MAX(CAST(SUBSTRING(st, ?) AS UNSIGNED)) AS max_st FROM predracun WHERE st LIKE CONCAT(?, '%')");
    $startPos = $prefixLength + 1;
    $stmt->bind_param("is", $startPos, $prefix);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $max_st = isset($row['max_st']) ? (int) $row['max_st'] : 0;
    $stmt->close();

    $novi_st = $prefix . str_pad($max_st + 1, 8, '0', STR_PAD_LEFT);

    $query = "INSERT INTO predracun (uporabnik_id, st, dt, izdan, skupna_cena, koncna_cena, ime_kupca, priimek_kupca) VALUES (?, ?, NOW(), 0, 0, 0, ?, ?)";
    $stmt = $link->prepare($query);
    $stmt->bind_param("isss", $uporabnik_id, $novi_st, $ime_kupca, $priimek_kupca);
    $stmt->execute();

    $_SESSION['racunId'] = $stmt->insert_id;

    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['dodaj_artikel'])) {
    $artikel_id = $_POST['artikel_id'];
    $kolicina = 1;
    $predracun_id = $_SESSION['racunId'];
    $stmt = $link->prepare("INSERT INTO artikel_predracun (artikel_id, kolicina, predracun_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $artikel_id, $kolicina, $predracun_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['posodobi_kolicino'])) {
    $artikel_predracun_id = $_POST['artikel_predracun_id'];
    $nova_kolicina = $_POST['kolicina'];
    $stmt = $link->prepare("UPDATE artikel_predracun SET kolicina = ? WHERE id = ?");
    $stmt->bind_param("ii", $nova_kolicina, $artikel_predracun_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['izbris_artikel'])) {
    $artikel_predracun_id = $_POST['artikel_predracun_id'];
    $stmt = $link->prepare("DELETE FROM artikel_predracun WHERE id = ?");
    $stmt->bind_param("i", $artikel_predracun_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['uporabi_popust']) && isset($_POST['popust'])) {
    $popust = floatval($_POST['popust']);
    if ($popust > 0 && $popust <= 100) {
        //$novaCena = $skupnaCena - ($skupnaCena * $popust / 100);
        $popustMult = 1 - $popust / 100;
        $racunId = $_SESSION['racunId'];

        $stmt = $link->prepare("UPDATE predracun SET koncna_cena = skupna_cena * ? , popust = ? WHERE id = ?");
        $stmt->bind_param("ddi", $popustMult, $popust, $racunId);
        $stmt->execute();

        $stmt->close();
    }

    //echo "IDK" . "--" - $popust . "--" .  $popustMult;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


$racunId = -1;
if (isset($_SESSION['racunId'])) {
    $racunId = $_SESSION['racunId'];
}

$izdan = 0;
$popust = 0;
if ($racunId >= 0) {
    $sql = 'SELECT izdan, popust FROM predracun WHERE id = ' . $racunId . ";";
    $result = $link->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $izdan = $row['izdan'];
        $popust = $row['popust'];
    }
}

echo $popust;




echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "<br>";
echo "<a href='odjava.php'>Odjava</a> | <a href='pregled_predracunov_uporabnik.php'>Moji predračuni</a>";
if ($isAdmin) {
    echo " | <a href='admin.php'>Nazaj na admin meni</a>";
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
        <div class="left-panel">
            <h3>Izpis predračuna</h3>
            <table>
                <thead>
                    <tr>
                        <th>Artikel</th>
                        <th>Količina</th>
                        <th>Cena</th>
                        <th>Skupaj</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $skupnaCena = 0;
                    if (isset($_SESSION['racunId'])) {
                        $racunId = $_SESSION['racunId'];
                        $query = "SELECT r.id as artikel_predracun_id, a.ime, a.cena, r.kolicina
                                  FROM artikli a
                                  INNER JOIN artikel_predracun r ON a.id = r.artikel_id
                                  WHERE r.predracun_id = ?";
                        $stmt = $link->prepare($query);
                        $stmt->bind_param("i", $racunId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $skupaj = $row['cena'] * $row['kolicina'];
                            $skupnaCena += $skupaj;
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                            echo "<td>
                                <form method='post'>
                                    <input type='number' name='kolicina' value='{$row['kolicina']}' min='1' style='width:60px'>
                                    <input type='hidden' name='artikel_predracun_id' value='{$row['artikel_predracun_id']}'>
                                    <button type='submit' name='posodobi_kolicino'>✓</button>
                                </form>
                              </td>";
                            echo "<td>" . number_format($row['cena'], 2) . " €</td>";
                            echo "<td>" . number_format($skupaj, 2) . " €</td>";
                            echo "<td>
                                <form method='post'>
                                    <input type='hidden' name='artikel_predracun_id' value='{$row['artikel_predracun_id']}'>
                                    <button type='submit' name='izbris_artikel'>🗑</button>
                                </form>
                              </td>";
                            echo "</tr>";
                        }
                        $stmt->close();
                    }
                    ?>
                </tbody>
            </table>
            <h4>Skupni znesek: <span id="skupni-znesek"><?php echo number_format($skupnaCena, 2); ?></span> €</h4>

            <?php
            if ($popust > 0)
                echo '<h4>S popustom: <span id="skupni-znesek">' . number_format($skupnaCena * (1 - $popust / 100), 2) . '</span> €</h4>';
            ?>

            <form method="POST" class="popust-form">
                <label for="popust">Vnesite popust (%):</label>
                <input type="number" name="popust" id="popust" min="0" max="100" step="0.001">
                <button type="submit" name="uporabi_popust" class="btn akcija">Uporabi popust</button>
                <br>
                <a href="generiraj_pdf.php">PDF</a>
            </form>

            <?php
            // if (isset($_POST['uporabi_popust']) && isset($_POST['popust'])) {
            //     $popust = floatval($_POST['popust']);
            //     if ($popust > 0 && $popust <= 100) {
            //         $novaCena = $skupnaCena - ($skupnaCena * $popust / 100);
            //         echo "<h4>Popust: {$popust}%</h4>";
            //         echo "<h4>Nova cena po popustu: " . number_format($novaCena, 2) . " €</h4>";

            //         $racunId = $_SESSION['racunId'];
            //         $stmt = $link->prepare("UPDATE predracun SET skupna_cena = ? WHERE id = ?");
            //         $stmt->bind_param("id", $skupnaCena, $racunId);
            //         $stmt = $link->prepare("UPDATE predracun SET koncna_cena = ? WHERE id = ?");
            //         $stmt->bind_param("di", $novaCena, $racunId);
            //         $stmt->execute();
            //         $stmt->close();
            //     }
            // }
            ?>

            <div class="kategorije">
                <h3>Kategorije</h3>
                <form method="post">
                    <?php
                    $result = mysqli_query($link, "SELECT * FROM kategorije");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<button type='submit' name='kategorija_id' value='{$row['id']}'>{$row['ime']}</button>";
                    }
                    ?>
                </form>
            </div>
        </div>

        <div class="right-panel">
            <?php
            $imeKupca = '';
            $priimekKupca = '';

            if (isset($_SESSION['racunId'])) {
                $stmt = $link->prepare("SELECT ime_kupca, priimek_kupca FROM predracun WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['racunId']);
                $stmt->execute();
                $stmt->bind_result($imeKupca, $priimekKupca);
                $stmt->fetch();
                $stmt->close();
            }
            ?>
            <form method="POST">
                <h4>Ustvari nov račun</h4>
                <input type="text" name="ime_kupca" placeholder="Ime kupca"
                    value="<?php echo htmlspecialchars($imeKupca); ?>" required>
                <input type="text" name="priimek_kupca" placeholder="Priimek kupca"
                    value="<?php echo htmlspecialchars($priimekKupca); ?>" required>
                <button type="submit" name="sub" value="novracun" class="btn akcija">Nov račun</button>
            </form>


            <form action="#" method="POST">


                <?php


                if ($izdan < 1) {
                    echo '<button type="submit" name="shrani" class="izdaja">Shrani račun</button>';
                    echo '<button type="submit" name="izdaja" class="izdaja">Izdaj račun</button>';
                }
                // else echo '<button name="izdaja" class="izdaja">Racun je ze izdan</button>';
                ?>
            </form>

            <?php
            if (isset($_POST['kategorija_id'])) {
                $kategorija_id = $_POST['kategorija_id'];
                $query = "SELECT * FROM artikli WHERE kategorija_id = ?";
                $stmt = $link->prepare($query);
                $stmt->bind_param("i", $kategorija_id);
                $stmt->execute();
                $result = $stmt->get_result();

                echo "<h4>Izdelki:</h4><table>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                    echo "<td>" . number_format($row['cena'], 2) . " €</td>";
                    echo "<td>
                            <form method='post'>
                                <input type='hidden' name='artikel_id' value='{$row['id']}'>
                                <button type='submit' name='dodaj_artikel'>Dodaj</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
                $stmt->close();
            }
            ?>
        </div>
    </div>
</body>

</html>