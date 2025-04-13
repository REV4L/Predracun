<?php
ob_start();
require_once 'baza.php';
require_once __DIR__ . '../FPDF-master/fpdf.php';

session_start();

if (!isset($_SESSION['racunId'])) {
    die("Ni odprtega računa.");
}

$racunId = $_SESSION['racunId'];
$ime = $_SESSION['ime'] ?? 'Neznano';
$priimek = $_SESSION['priimek'] ?? '';
$firma = "Moja Firma d.o.o.";

$query = "SELECT p.st, p.dt, a.ime, a.cena, r.kolicina
          FROM predracun p
          INNER JOIN artikel_predracun r ON p.id = r.predracun_id
          INNER JOIN artikli a ON a.id = r.artikel_id
          WHERE p.id = ?";
$stmt = $link->prepare($query);
if (!$stmt) {
    die("Napaka pri pripravi poizvedbe: " . $link->error);
}
$stmt->bind_param("i", $racunId);
if (!$stmt->execute()) {
    die("Napaka pri izvajanju poizvedbe: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Ni artiklov za ta račun.");
}

// Pridobivanje popusta iz baze
$queryPopust = "SELECT koncna_cena FROM predracun WHERE id = ?";
$stmtPopust = $link->prepare($queryPopust);
$stmtPopust->bind_param("i", $racunId);
$stmtPopust->execute();
$stmtPopust->bind_result($koncnaCena);
$stmtPopust->fetch();
$stmtPopust->close();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSans.php');
$pdf->SetFont('DejaVu','',12);

$pdf->Cell(0, 10, $firma, 0, 1);
$pdf->Cell(0, 10, "Predračun št.: " . $racunId, 0, 1);
$pdf->Cell(0, 10, "Prodajalec: " . $ime . " " . $priimek, 0, 1);

$datum = '';
$skupnaCena = 0;

$pdf->Ln(5);
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(80, 10, "Artikel", 1);
$pdf->Cell(30, 10, "Količina", 1);
$pdf->Cell(30, 10, "Cena", 1);
$pdf->Cell(30, 10, "Skupaj", 1);
$pdf->Ln();

$pdf->SetFont('DejaVu', '', 12);
while ($row = $result->fetch_assoc()) {
    if ($datum === '') {
        $datum = date("d.m.Y", strtotime($row['dt']));
    }

    $artikel = $row['ime'];
    $kolicina = $row['kolicina'];
    $cena = $row['cena'];
    $skupaj = $kolicina * $cena;
    $skupnaCena += $skupaj;

    $pdf->Cell(80, 10, $artikel, 1);
    $pdf->Cell(30, 10, $kolicina, 1);
    $pdf->Cell(30, 10, number_format($cena, 2) . "€", 1);
    $pdf->Cell(30, 10, number_format($skupaj, 2) . "€", 1);
    $pdf->Ln();
}

// Skupaj + datum
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(140, 10, "Skupaj", 1);
$pdf->Cell(30, 10, number_format($skupnaCena, 2) . "€", 1);
$pdf->Ln(10);

$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, "Datum: " . $datum, 0, 1);
$pdf->Cell(0, 10, "Končna cena: " . number_format($koncnaCena, 2) . "€", 0, 1);

ob_end_clean();
$pdf->Output("D", "racun_" . $racunId . ".pdf");
exit;
?>
