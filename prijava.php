<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
    <div class="form-container">
        <form action="preveri_prijavo.php" method="post">
            <h2>Prijava</h2>
            <span>Vnesite e-naslov: </span>
            <input type="text" name="user" required /><br>
            <span>Vnesite geslo: </span>
            <input type="password" name="pas" required /><br>

            <div id="center"><input type="submit" value="Prijava" id="submit"/></div>
            <div id="center">
                <a href="index.php" id="link">Nazaj na uvodno stran</a>
            </div>
        </form>
    </div>

    <footer>
        <p>&#169; Valentin Ozimic in Nikola Marinković 2025</p>
    </footer>
</body>
</html>
