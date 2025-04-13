<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['ime']) || !isset($_SESSION['priimek'])) {
    header("Location: prijava.php");
    exit();
}

echo "<div class='pozdrav'>Prijavljeni ste kot " . $_SESSION['ime'] . " " . $_SESSION['priimek'] . "<br><a href='odjava.php'>Odjava</a></div>";

if ($_SESSION['role'] == 'a') {
    echo "<div><a href='admin.php'>Nazaj na admin panel</a></div>";
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
                        $query = "SELECT r.id AS rId, a.ime, a.cena 
                                  FROM artikli a 
                                  INNER JOIN artikli1 r ON a.id = r.artikel_id 
                                  WHERE r.predracun_id = $racunId";

                        $result = mysqli_query($link, $query);

                        $skupnaCena = 0;
                        while ($row = mysqli_fetch_assoc($result)) {
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
            <form action="#" method="POST">
                <input type="submit" name="sub" value="novracun">
            </form>
            <form action="#" method="POST">
                <input type="submit" name="izdaja" value="izdaja_racuna">
            </form>

            <?php
            if (isset($_POST['sub']) && $_POST['sub'] == 'novracun') {
                $uporabnik_id = $_SESSION['id'];  // Assuming session stores user id
                $query = "INSERT INTO predracun (st, dt, uporabnik_id) VALUES ('nov', NOW(), $uporabnik_id)";
                $result = mysqli_query($link, $query);

                if ($result) {
                    $query = "SELECT id FROM predracun ORDER BY id DESC LIMIT 1";
                    $result = mysqli_query($link, $query);
                    $row = mysqli_fetch_assoc($result);
                    $_SESSION['racunId'] = $row['id'];
                } else {
                    echo "Napaka pri ustvarjanju novega predračuna.";
                }

                unset($_POST['sub']);
            }

            if (isset($_POST['izdaja']) && $_POST['izdaja'] == 'izdaja_racuna') {
                unset($_SESSION['racunId']);
                unset($_POST['izdaja']);
            }
            ?>
        </div>
    </div>
</body>
</html>
