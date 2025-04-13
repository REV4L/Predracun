<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

// Preverba, ali je uporabnik admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'a';

echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "<br>";
echo "<a href='odjava.php'>Odjava</a>";
if ($isAdmin) {
    echo " | <a href='admin.php'>Nazaj na admin panel</a>";
}
echo "</div>";
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Blagajna za bar</title>
    <link rel="stylesheet" href="blagajna.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="predracun" id="predracun">
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
                    <tbody id="predracun-body">
                    </tbody>
                    <?php 
                        if (isset($_SESSION['racunId'])) {
                            $racunId = $_SESSION['racunId'];
                            $query = "SELECT r.id as rId, p.id, p.ime, p.cena FROM pijace p
                                      INNER JOIN pijace_racun r ON p.id = r.pijace_id
                                      INNER JOIN racun ra ON ra.id = r.racun_id
                                      WHERE ra.id = $racunId";
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
                                echo "<td><form action='izbris_racun.php' method='post'>
                                          <button type='submit' name='rId' value='$rId'>Izbris</button>
                                      </form></td>";
                                echo "</tr>";
                            }
                        }
                    ?>
                </table>
                <h4>Skupni znesek: <span id="skupni-znesek"><?php echo $skupnaCena; ?></span> €</h4>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="kategorije">
                <form method="post" action="">
                    Kategorija:
                    <?php
                        $query = "SELECT id, ime FROM kategorija";
                        $result = mysqli_query($link, $query);
                        if (!$result) {
                            die("Napaka pri pridobivanju kategorij: " . mysqli_error($link));
                        }
                        echo '<select name="kategorija_id" required>';
                        while ($row = mysqli_fetch_assoc($result)) {
                            $id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                            $ime = htmlspecialchars($row['ime'], ENT_QUOTES, 'UTF-8');
                            echo "<option value=\"$id\">$ime</option>";
                        }
                        echo '</select>';
                    ?>
                    <input type="submit" name="submit" value="Prikaži">
                </form>

                <?php
                    if (isset($_POST['submit'])) {
                        $kategorija_id = $_POST['kategorija_id'];
                        $query = "SELECT * FROM pijace WHERE kategorija_id = ?";
                        $stmt = $link->prepare($query);
                        $stmt->bind_param("i", $kategorija_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        echo '<table border="1" style="border-collapse: collapse">';
                        echo '<tr><th>ID</th><th>Ime</th><th>Cena</th><th>Kategorija ID</th><th>Akcija</th></tr>';

                        while ($row = mysqli_fetch_assoc($result)) {
                            $itemId = $row['id'];
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ime']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['cena']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['kategorija_id']) . "</td>";
                            echo "<td>
                                    <form action='dodaj.php' method='POST' style='display:inline;'>
                                        <input type='hidden' name='id' value='" . $row['id'] . "'>
                                        <button type='submit' name='itemId' value='$itemId'>Dodaj</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        $stmt->close();
                    }
                ?>
            </div>

            <form action="#" method="POST">
                <input type="submit" name="sub" value="Nov Račun">
            </form>

            <form action="#" method="POST">
                <input type="submit" name="izdaja" value="Izdaja Računa">
            </form>

            <?php
            if (isset($_POST['sub']) && $_POST['sub'] == 'Nov Račun') {
                $uporabnik_id = $_SESSION['uporabnik_id']; 
                $query = "INSERT INTO racun (miza_id, natakar_id, datum) VALUES (NULL, $uporabnik_id, NOW())";
                $result = mysqli_query($link, $query);

                if ($result) {
                    $query = "SELECT id FROM racun ORDER BY id DESC LIMIT 1";
                    $result = mysqli_query($link, $query);
                    $row = mysqli_fetch_assoc($result);
                    $_SESSION['racunId'] = $row['id'];
                } else {
                    echo "Napaka pri ustvarjanju novega računa: " . $stmt->error;
                }
                unset($_POST['sub']);
            }

            if (isset($_POST['izdaja']) && $_POST['izdaja'] == 'Izdaja Računa') {
                unset($_SESSION['racunId']);
                unset($_POST['izdaja']);
            }
            ?>
        </div>
    </div>
</body>
</html>
