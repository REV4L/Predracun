<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
    <div class="c">
        <form action="preveri_prijavo.php" method="post">
            
            <span>Vnesite e-naslov: </span><input type="text" name="user" /><br>
            <span>Vnesite geslo: </span><input type="password" name="pas" /><br>

            <div id="center"><input type="submit" value="Prijava" id="submit"/></div>
            <a href="index.php"id="link">Nazaj na uvodno stran</a><br>
        </form>
    </div>
    <footer>
        <p>&#169; Valentin Ozimic and Nikola Marinković</p>
        <p>nikola.marinkovic@scv.si</p>
</footer>
</body>
</html>