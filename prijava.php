<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="prijava.css">
</head>
<body>
    <div class="form-container">
        <form action="preveri_prijavo.php" method="post">
            
            <span>Vnesite e-naslov: </span><input type="text" name="user" /><br>
            <span>Vnesite geslo: </span><input type="password" name="pas" /><br>

            <div id="center"><input type="submit" value="Prijava" id="submit"/></div>
            <a href="index.php"id="link">Nazaj na uvodno stran</a><br>
        </form>
    </div>
</body>
</html>