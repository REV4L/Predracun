<?php
require_once 'baza.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Nepravilen dostop.");
}

$id = $_GET['id'];

$query = "SELECT * FROM uporabniki WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Uporabnik ne obstaja.");
}

$row = $result->fetch_assoc();

$uspesno = false;
$napaka = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $priimek = $_POST['priimek'];
    $telefonska = $_POST['telefon'];
    $email = $_POST['email'];

    if (empty($ime) || empty($priimek) || empty($telefonska) || empty($email)) {
        $napaka = "Vsa polja morajo biti izpolnjena.";
    } else {
        $query = "UPDATE uporabniki SET ime = ?, priimek = ?, telefon = ?, email = ? WHERE id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("ssssi", $ime, $priimek, $telefonska, $email, $id);

        if ($stmt->execute()) {
            $uspesno = true;
        } else {
            $napaka = "Napaka pri posodabljanju uporabnika: " . mysqli_error($link);
        }

        $stmt->close();
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Posodobitev uporabnika</title>
    <link rel="stylesheet" href="prijava.css">
    <style>
        .obvestilo {
            text-align: center;
            margin: 20px auto;
            padding: 15px 20px;
            width: fit-content;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($uspesno): ?>
            <div class="obvestilo success">Uporabnik uspešno posodobljen.</div>
            <script>
                setTimeout(function() {
                    window.location.href = "izpis_uporabnikov.php";
                }, 3000);
            </script>
        <?php endif; ?>

        <?php if (!empty($napaka)): ?>
            <div class="obvestilo error"><?php echo $napaka; ?></div>
        <?php endif; ?>

        <h1>Posodobitev uporabnika</h1>
        <form action="#" method="post">
            <span><label for="ime">Ime:</label></span>
            <input type="text" name="ime" value="<?php echo htmlspecialchars($row['ime']); ?>" required>
            <br>
            <span><label for="priimek">Priimek:</label></span>
            <input type="text" name="priimek" value="<?php echo htmlspecialchars($row['priimek']); ?>" required>
            <br>
            <span><label for="telefon">Telefonska:</label></span>
            <input type="text" name="telefon" value="<?php echo htmlspecialchars($row['telefon']); ?>" required>
            <br>
            <span><label for="email">E-mail:</label></span>
            <input type="text" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
            <br>
            <a href='zamenjaj_geslo.php?id=<?php echo $id; ?>' id="link">Zamenjaj geslo</a>
            <div id="center">
                <input type="submit" value="Posodobi" id="submit">
            </div>
        </form>
        <br>
        <a href='izpis_uporabnikov.php' id="link">Nazaj</a>
    </div>
</body>
</html>
