<?php
require_once 'baza.php';
require_once 'seja.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'a') {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Registracija uporabnika</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
    <div class="form-container">
        <h1>Registracija uporabnika</h1>
        <form action="vstavi_uporabnika_redir.php" method="post">
            <span>Ime: </span><input type="text" name="ime" placeholder="Ime" required> <br>
            <span>Priimek: </span><input type="text" name="priimek" placeholder="Priimek" required> <br>
            <span>Telefonska: </span><input type="text" name="telefonska" placeholder="Telefonska" required> <br>
            <span>E-mail: </span><input type="text" name="username" placeholder="Uporabniško ime" required> <br>
            <span>Geslo: </span><input type="password" name="pass" placeholder="Geslo" required> <br>

            <span>Pozicija: </span>
            <select name="role">
                <option value="a">Admin</option>
                <option value="p">Uporabnik</option>
            </select>
            <br>
            <div id="center">
                <input type="submit" name="sub" value="Registracija" id="submit">
            </div>
        </form>
        <a href="admin.php" id="link">Nazaj</a><br>
    </div>
</body>
</html>
