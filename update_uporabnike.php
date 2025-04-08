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

$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $priimek = $_POST['priimek'];
    $telefonska = $_POST['telefon'];
    $email = $_POST['email'];
    $pasw = $_POST['pasw'];

    if (empty($ime) || empty($priimek) || empty($telefonska) || empty($email) || empty($pasw)) {
        die("Vsa polja morajo biti izpolnjena.");
    }

    // Encrypt the password with SHA1
    $hashed_pass = sha1($pasw);

    $query = "UPDATE uporabniki SET ime = ?, priimek = ?, telefon = ?, email = ?, pasw = ? WHERE id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("sssssi", $ime, $priimek, $telefonska, $email, $hashed_pass, $id);

    if ($stmt->execute()) {
        echo "Uporabnik uspešno posodobljen.";
        header("refresh: 3; URL=izpis_uporabnikov.php");
    } else {
        echo "Napaka pri posodabljanju uporabnika: " . mysqli_error($link);
    }

    $stmt->close();
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Posodobitev uporabnika</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
    <div class="form-container">
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
        <span><label for="email">E-mail;</label></span>
        <input type="text" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
        <br>
        <input type="button" href="zamenjaj_geslo.php">Zamenjaj geslo</button>
        <div id="center">
        <input type="submit" value="Posodobi" id="submit">
        </div>
    </form>
    <br>
    <a href='izpis_uporabnikov.php' id="link">Nazaj</a>
    </div>
</body>
</html>