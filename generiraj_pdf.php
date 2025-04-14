<?php
require_once 'baza.php';
require_once 'fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['racunId'])) {
    die("Ni odprtega računa.");
}

$racunId = $_SESSION['racunId'];
$ime = $_SESSION['ime'] ?? 'Neznano';
$priimek = $_SESSION['priimek'] ?? '';
$firma = "Moja Firma d.o.o.";

$query = "SELECT p.st, a.ime, a.cena, r.kolicina
          FROM predracun p
          INNER JOIN artikel_predracun r ON p.id = r.predracun_id
          INNER JOIN artikli a ON a.id = r.artikel_id
          WHERE p.id = ?";

$stmt = $link->prepare($query);
if (!$stmt) die("Napaka pri pripravi poizvedbe: " . $link->error);
$stmt->bind_param("i", $racunId);
if (!$stmt->execute()) die("Napaka pri izvajanju poizvedbe: " . $stmt->error);
$result = $stmt->get_result();

// ⬇️ Uporaba DejaVuSans za podporo UTF-8 ⬇️
$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVuSans', '', 'DejaVuSans.php');
$pdf->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.php');

// Naslov
$pdf->SetFont('DejaVuSans', 'B', 16);
$pdf->Cell(0, 10, $firma, 0, 1);
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->Cell(0, 10, "Predračun št.: " . $racunId, 0, 1);
$pdf->Cell(0, 10, "Prodajalec: " . $ime . " " . $priimek, 0, 1);
$pdf->Ln(5);

// Tabela
$pdf->SetFont('DejaVuSans', 'B', 12);
$pdf->Cell(80, 10, "Artikel", 1);
$pdf->Cell(30, 10, "Količina", 1);
$pdf->Cell(30, 10, "Cena", 1);
$pdf->Cell(30, 10, "Skupaj", 1);
$pdf->Ln();

$skupnaCena = 0;
$pdf->SetFont('DejaVuSans', '', 12);
while ($row = $result->fetch_assoc()) {
    $artikel = $row['ime'];
    $kolicina = $row['kolicina'];
    $cena = $row['cena'];
    $skupaj = $kolicina * $cena;
    $skupnaCena += $skupaj;

    $pdf->Cell(80, 10, $artikel, 1);
    $pdf->Cell(30, 10, $kolicina, 1);
    $pdf->Cell(30, 10, number_format($cena, 2) . " €", 1);
    $pdf->Cell(30, 10, number_format($skupaj, 2) . " €", 1);
    $pdf->Ln();
}

// Skupaj
$pdf->SetFont('DejaVuSans', 'B', 12);
$pdf->Cell(140, 10, "Skupaj", 1);
$pdf->Cell(30, 10, number_format($skupnaCena, 2) . " €", 1);

// Output
ob_clean();
$pdf->Output("D", "racun_" . $racunId . ".pdf");
exit;
?>
