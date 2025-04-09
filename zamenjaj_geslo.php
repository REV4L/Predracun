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

$query = "SELECT * FROM uporabniki WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Uporabnik ne obstaja.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_geslo = $_POST['novo_geslo'];

    if (empty($novo_geslo)) {
        echo "Polje za geslo ne sme biti prazno.";
    } else {
        $hashed = sha1($novo_geslo);

        $query = "UPDATE uporabniki SET pasw = ? WHERE id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("si", $hashed, $id);

        if ($stmt->execute()) {
            echo "Geslo uspešno posodobljeno.";
            header("refresh: 3; URL=uredi_uporabnika.php?id=" . $id);
        } else {
            echo "Napaka pri posodabljanju gesla: " . mysqli_error($link);
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
    <title>Zamenjava gesla</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
<div class="form-container">
    <h1>Zamenjava gesla</h1>
    <form method="post" action="">
        <label for="novo_geslo">Novo geslo:</label>
        <input type="password" name="novo_geslo" required>
        <div id="center">
            <input type="submit" value="Posodobi geslo" id="submit">
        </div>
    </form>
    <br>
    <a href='uredi_uporabnika.php?id=<?php echo $id; ?>' id="link">Nazaj</a>
</div>
</body>
</html>
