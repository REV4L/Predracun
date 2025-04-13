<?php
require_once 'baza.php';
session_start();

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'a';

echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "<br>";
echo "<a href='odjava.php'>Odjava</a>";
echo " | <a href='pregled_predracunov_uporabnik.php'>Moji predračuni</a>";
if ($isAdmin) {
    echo " | <a href='admin.php'>Nazaj na admin panel</a>";
}
echo "</div>";

// Nov račun
if (isset($_POST['sub']) && $_POST['sub'] == 'novracun') {
    $uporabnik_id = $_SESSION['uporabnik_id'];
    $query = "INSERT INTO predracun (uporabnik_id, st, dt, izdan, skupna_cena, koncna_cena) VALUES (?, 'NA', NOW(), 0, 0, 0)";
    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $uporabnik_id);
    $stmt->execute();
    $_SESSION['racunId'] = $stmt->insert_id;
    $stmt->close();
}

// Dodaj artikel
if (isset($_POST['dodaj_artikel'])) {
    $artikel_id = $_POST['artikel_id'];
    $kolicina = 1;
    $predracun_id = $_SESSION['racunId'];
    $stmt = $link->prepare("INSERT INTO artikel_predracun (artikel_id, kolicina, predracun_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $artikel_id, $kolicina, $predracun_id);
    $stmt->execute();
    $stmt->close();
}

// Posodobi količino
if (isset($_POST['posodobi_kolicino'])) {
    $artikel_predracun_id = $_POST['artikel_predracun_id'];
    $nova_kolicina = $_POST['kolicina'];
    $stmt = $link->prepare("UPDATE artikel_predracun SET kolicina = ? WHERE id = ?");
    $stmt->bind_param("ii", $nova_kolicina, $artikel_predracun_id);
    $stmt->execute();
    $stmt->close();
}

// Izbriši artikel
if (isset($_POST['izbris_artikel'])) {
    $artikel_predracun_id = $_POST['artikel_predracun_id'];
    $stmt = $link->prepare("DELETE FROM artikel_predracun WHERE id = ?");
    $stmt->bind_param("i", $artikel_predracun_id);
    $stmt->execute();
    $stmt->close();
}

// Izdaja računa
if (isset($_POST['izdaja']) && $_POST['izdaja'] == 'izdaja_racuna') {
    $racunId = $_SESSION['racunId'];
    $query = "SELECT a.cena, r.kolicina FROM artikli a 
              INNER JOIN artikel_predracun r ON a.id = r.artikel_id 
              WHERE r.predracun_id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $racunId);
    $stmt->execute();
    $result = $stmt->get_result();
    $skupna = 0;
    while ($row = $result->fetch_assoc()) {
        $skupna += $row['cena'] * $row['kolicina'];
    }
    $stmt->close();

    $stmt = $link->prepare("UPDATE predracun SET izdan = 1, skupna_cena = ?, koncna_cena = ? WHERE id = ?");
    $stmt->bind_param("ddi", $skupna, $skupna, $racunId);
    $stmt->execute();
    $stmt->close();
    unset($_SESSION['racunId']);
}
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
        <div class="predracun">
            <h3>Izpis predračuna</h3>
            <table>
                <thead>
                <tr><th>Artikel</th><th>Količina</th><th>Cena</th><th>Skupaj</th><th>Akcija</th></tr>
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
        </div>

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
        <form method="POST">
            <button type="submit" name="sub" value="novracun" class="btn akcija">Nov račun</button>
        </form>
        <form method="POST">
            <button type="submit" name="izdaja" value="izdaja_racuna" class="btn akcija izdaj">Izdaj račun</button>
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
