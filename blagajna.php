<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'a';

echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "<br>";
echo "<a href='odjava.php'>Odjava</a>";
echo " | <a href='pregled_predracunov_uporabnik.php'>Moji predračuni</a>";
if ($isAdmin) echo " | <a href='admin.php'>Nazaj na admin panel</a>";
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
        <div class="predracun">
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
                    $query = "SELECT r.id as rId, p.ime, p.cena, r.kolicina
                              FROM artikli p
                              INNER JOIN artikli1 r ON p.id = r.artikel_id
                              WHERE r.predracun_id = $racunId";
                    $result = mysqli_query($link, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $skupaj = $row['cena'] * $row['kolicina'];
                        $skupnaCena += $skupaj;

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                        echo "<td>
                                <form action='update_kolicina.php' method='post' style='display:inline;'>
                                    <input type='hidden' name='rId' value='{$row['rId']}'>
                                    <input type='number' name='kolicina' value='{$row['kolicina']}' min='1' onchange='this.form.submit()'>
                                </form>
                              </td>";
                        echo "<td>" . htmlspecialchars($row['cena']) . "€</td>";
                        echo "<td>" . number_format($skupaj, 2) . "€</td>";
                        echo "<td>
                                <form action='izbris_racun.php' method='post'>
                                    <button type='submit' name='rId' value='{$row['rId']}'>Izbriši</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                }
                ?>
                </tbody>
            </table>
            <h4>Skupni znesek: <span id="skupni-znesek"><?php echo number_format($skupnaCena, 2); ?> €</span></h4>
        </div>

        <div class="kategorije">
            <h3>Kategorije</h3>
            <form method="POST">
                <?php
                $query = "SELECT * FROM kategorije";
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<button type='submit' name='kategorija_id' value='{$row['id']}' class='kategorija-btn'>"
                         . htmlspecialchars($row['ime']) . "</button>";
                }
                ?>
            </form>
        </div>
    </div>

    <div class="right-panel">
        <form method="POST">
            <button type="submit" name="sub" value="novracun" class="action-btn">🧾 Nov račun</button>
        </form>
        <form method="POST">
            <button type="submit" name="izdaja" value="izdaja_racuna" class="action-btn izdaja">✅ Izdaja računa</button>
        </form>

        <?php
        if (isset($_POST['sub']) && $_POST['sub'] === 'novracun') {
            $uporabnik_id = $_SESSION['uporabnik_id'];
            $query = "INSERT INTO predracun (uporabnik_id, st, dt, izdan, skupna_cena, koncna_cena)
                      VALUES ($uporabnik_id, 'NA', NOW(), 0, 0, 0)";
            $result = mysqli_query($link, $query);
            if ($result) {
                $_SESSION['racunId'] = mysqli_insert_id($link);
            } else {
                echo "Napaka: " . mysqli_error($link);
            }
        }

        if (isset($_POST['izdaja']) && $_POST['izdaja'] === 'izdaja_racuna') {
            if (isset($_SESSION['racunId'])) {
                $racunId = $_SESSION['racunId'];
                mysqli_query($link, "UPDATE predracun SET izdan = 1, skupna_cena = $skupnaCena, koncna_cena = $skupnaCena WHERE id = $racunId");
                unset($_SESSION['racunId']);
            }
        }

        if (isset($_POST['kategorija_id'])) {
            $kategorija_id = $_POST['kategorija_id'];
            $query = "SELECT * FROM artikli WHERE kategorija_id = $kategorija_id";
            $result = mysqli_query($link, $query);
            if ($result) {
                echo "<h4>Izdelki:</h4><table><tr><th>Ime</th><th>Cena</th><th>Akcija</th></tr>";
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cena']) . "€</td>";
                    echo "<td>
                            <form method='post' action='dodaj_artikel.php'>
                                <input type='hidden' name='artikel_id' value='{$row['id']}'>
                                <input type='submit' value='Dodaj'>
                            </form>
                          </td>";
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
