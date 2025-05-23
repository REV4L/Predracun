<?php
require_once 'baza.php';

if (!isset($_POST["sub"])) die("Nepravilen dostop");

if (empty($_POST["ime"]) || empty($_POST["priimek"]) || empty($_POST["username"]) || empty($_POST["pass"]) || empty($_POST['telefonska']) || empty($_POST["role"])) {
    die("Nepravilen vnos. Vsa polja morajo biti izpolnjena.");
}

$ime = $_POST["ime"];
$priimek = $_POST["priimek"];
$username = $_POST["username"];
$pass = $_POST["pass"];
$telefonska = $_POST['telefonska'];
$role = $_POST["role"]; 

$query = "SELECT * FROM uporabniki WHERE username = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<a href='admin.php'>Nazaj</a><br>";
    die("Uporabniško ime že obstaja. Prosim izberite drugo uporabniško ime.");
}

$stmt->close();

$sql = "INSERT INTO uporabniki (ime, priimek, telefon, email, pasw, role) VALUES (?, ?, ?, ?, sha1(?), ?)";
$stmt = $link->prepare($sql);
$stmt->bind_param("ssssss", $ime, $priimek, $telefonska, $username, $pass, $role);

if ($stmt->execute()) {
    echo "Uspešno izvedeno: <br>" . htmlspecialchars($sql) . "<br>";
} else {
    echo "Napaka: " . htmlspecialchars(mysqli_error($link));
}

$stmt->close();
mysqli_close($link);
?>

<br>
<a href="admin.php">Nazaj</a>
