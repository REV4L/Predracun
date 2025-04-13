<?php
require_once 'baza.php';
require_once 'seja.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Preveri, če je admin prijavljen
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: index.php");
    exit();
}

$uspesno = false;
$napaka = '';

// Če je obrazec oddan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sub"])) {
    // Preveri obvezna polja
    if (empty($_POST["ime"]) || empty($_POST["priimek"]) || empty($_POST["telefonska"]) || empty($_POST["username"]) || empty($_POST["pass"]) || empty($_POST["role"])) {
        $napaka = "Vsa polja morajo biti izpolnjena.";
    } else {
        $ime = $_POST["ime"];
        $priimek = $_POST["priimek"];
        $telefon = $_POST["telefonska"];
        $email = $_POST["username"];
        $geslo = $_POST["pass"];
        $role = $_POST["role"];

        // Preveri, če email že obstaja
        $stmt = $link->prepare("SELECT * FROM uporabniki WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $rezultat = $stmt->get_result();

        if ($rezultat->num_rows > 0) {
            $napaka = "Uporabniško ime (email) že obstaja.";
        } else {
            // Vstavi novega uporabnika
            $stmt = $link->prepare("INSERT INTO uporabniki (ime, priimek, email, pass, telefon, role) VALUES (?, ?, ?, sha1(?), ?, ?)");
            $stmt->bind_param("ssssss", $ime, $priimek, $email, $geslo, $telefon, $role);

            if ($stmt->execute()) {
                $uspesno = true;
            } else {
                $napaka = "Napaka pri vnosu: " . $link->error;
            }
        }

        $stmt->close();
    }

    $link->close();
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Registracija uporabnika</title>
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
        <h1>Registracija uporabnika</h1>

        <?php if ($uspesno): ?>
            <div class="obvestilo success">Uporabnik uspešno registriran.</div>
            <script>
                setTimeout(() => { window.location.href = 'izpis_uporabnikov.php'; }, 3000);
            </script>
        <?php elseif (!empty($napaka)): ?>
            <div class="obvestilo error"><?php echo $napaka; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <span>Ime: </span><input type="text" name="ime" placeholder="Ime" required><br>
            <span>Priimek: </span><input type="text" name="priimek" placeholder="Priimek" required><br>
            <span>Telefonska: </span><input type="text" name="telefonska" placeholder="Telefonska" required><br>
            <span>E-mail: </span><input type="text" name="username" placeholder="Uporabniško ime" required><br>
            <span>Geslo: </span><input type="password" name="pass" placeholder="Geslo" required><br>

            <span>Pozicija: </span>
            <select name="role" required>
                <option value="a">Admin</option>
                <option value="p">Uporabnik</option>
            </select><br>

            <div id="center">
                <input type="submit" name="sub" value="Registracija" id="submit">
            </div>
        </form>

        <a href="admin.php" id="link">Nazaj</a><br>
    </div>
</body>
</html>
