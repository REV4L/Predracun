<?php
if (isset($_POST['ime'], $_POST['cena'], $_POST['kolicina'], $_POST['kategorija_id'])) {
    $ime = $_POST['ime'];
    $cena = $_POST['cena'];
    $kolicina = $_POST['kolicina'];
    $kategorija_id = $_POST['kategorija_id'];
    $opis = isset($_POST['opis']) ? $_POST['opis'] : NULL; 

    if (!empty($ime) && !empty($cena) && !empty($kolicina) && !empty($kategorija_id)) {
        $query = "INSERT INTO artikli (ime, cena, kolicina, kategorija_id, opis) VALUES (?, ?, ?, ?, ?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("sdiss", $ime, $cena, $kolicina, $kategorija_id, $opis);
        $stmt->execute();
        $stmt->close();

        header("Location: admin.php"); 
        exit();
    } else {
        echo "Vsi podatki morajo biti vneseni!";
    }
}
