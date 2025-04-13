<?php
require_once 'baza.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Nepravilen dostop.");
}

$id = $_GET['id'];

// Za prikaz obvestil
$uspesno = false;
$napaka = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_geslo = $_POST['novo_geslo'];

    if (empty($novo_geslo)) {
        $napaka = "Polje za geslo ne sme biti prazno.";
    } else {
        $hashed = sha1($novo_geslo);

        $query = "UPDATE uporabniki SET pasw = ? WHERE id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("si", $hashed, $id);

        if ($stmt->execute()) {
            $uspesno = true;
        } else {
            $napaka = "Napaka pri posodabljanju gesla: " . mysqli_error($link);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamenjava gesla</title>
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
        <div class="obvestilo success">Geslo uspešno posodobljeno.</div>
        <script>
            setTimeout(function() {
                window.location.href = "izpis_uporanikov.php?id=<?php echo $id; ?>";
            }, 3000);
        </script>
    <?php endif; ?>

    <?php if (!empty($napaka)): ?>
        <div class="obvestilo error"><?php echo $napaka; ?></div>
    <?php endif; ?>

    <h1>Zamenjava gesla</h1>
    <form method="post" action="">
        <label for="novo_geslo">Novo geslo:</label>
        <input type="password" name="novo_geslo" required>
        <div id="center">
            <input type="submit" value="Posodobi geslo" id="submit">
        </div>
    </form>
    <br>
    <a href='izpis_uporabnikov.php?id=<?php echo $id; ?>' id="link">Nazaj</a>
</div>
</body>
</html>
