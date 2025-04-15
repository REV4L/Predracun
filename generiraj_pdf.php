<?php
require_once 'baza.php';
require_once 'seja.php';
require_once 'tfpdf/tfpdf.php'; // uporabi tFPDF (ne navaden FPDF)
session_start();

if (!isset($_SESSION['racunId'])) {
    die("Ni odprtega računa.");
}

$racunId = $_SESSION['racunId'];
$ime = $_SESSION['ime'] ?? 'Neznano';
$priimek = $_SESSION['priimek'] ?? '';
$firma = "Moja Firma d.o.o.";

// Pridobivanje trenutnega datuma
$datum = date('d.m.Y'); // Oblika: dd.mm.YYYY

$query = "SELECT p.st, a.ime, a.cena, r.kolicina
          FROM predracun p
          INNER JOIN artikel_predracun r ON p.id = r.predracun_id
          INNER JOIN artikli a ON a.id = r.artikel_id
          WHERE p.id = ?";

$stmt = $link->prepare($query);
if (!$stmt)
    die("Napaka pri pripravi poizvedbe: " . $link->error);
$stmt->bind_param("i", $racunId);
if (!$stmt->execute())
    die("Napaka pri izvajanju poizvedbe: " . $stmt->error);
$result = $stmt->get_result();

// Generiranje PDF
$pdf = new tFPDF();
$pdf->AddPage();

// Dodamo DejaVuSans pisavo z Unicode podporo
$pdf->AddFont('DejaVu', '', 'DejaVuSans.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->SetFont('DejaVu', 'B', 16);
$pdf->Cell(0, 10, $firma, 0, 1);
$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, "Predračun št.: " . $racunId, 0, 1);
$pdf->Cell(0, 10, "Datum: " . $datum, 0, 1); // Dodan datum
$pdf->Cell(0, 10, "Prodajalec: " . $ime . " " . $priimek, 0, 1);
$pdf->Ln(5);

// Tabela
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(80, 10, "Artikel", 1);
$pdf->Cell(30, 10, "Količina", 1);
$pdf->Cell(30, 10, "Cena", 1);
$pdf->Cell(30, 10, "Skupaj", 1);
$pdf->Ln();

$skupnaCena = 0;
$pdf->SetFont('DejaVu', '', 12);
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

// Skupna cena
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(140, 10, "Skupaj", 1);
$pdf->Cell(30, 10, number_format($skupnaCena, 2) . " €", 1);

// Output
ob_clean(); // Pomembno!
$pdf->Output("D", "racun_" . $racunId . ".pdf");

header("Location: blagajna.php");
