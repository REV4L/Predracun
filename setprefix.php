<?php
require_once 'baza.php';



if (isset($_GET["prefix"])) {
    $p = $_GET["prefix"];

    $query = "UPDATE settings SET prefix = ? WHERE 1=1";
    $stmt = $link->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $p);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Napaka pri pripravi poizvedbe.";
    }

    // Kratek premor (ni vedno potreben, lahko ga odstraniš)
    sleep(1);
    header("Location: pregled_predracunov.php");
    exit;
}
?>