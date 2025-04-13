<?php
require_once 'baza.php';
require_once 'fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['racunId'])) {
    die("Ni odprtega računa.");
}

$racunId = $_SESSION['racunId'];
$ime = $_SESSION['ime'];
$priimek = $_SESSION['priimek'];
$firma = "Moja Firma d.o.o.";

// Pridobi podatke o računih in artiklih
$query = "SELECT p.st, a.ime, a.cena, r.kolicina
          FROM predracun p
          INNER JOIN artikli1 r ON p.id = r.predracun_id
          INNER JOIN artikli a ON a.id = r.artikel_id
          WHERE p.id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $racunId);
$stmt->execute();
$result = $stmt->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, $firma, 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Predračun št.: " . $racunId, 0, 1);
$pdf->Cell(0, 10, "Prodajalec: " . $ime . " " . $priimek, 0, 1);
$pdf->Ln(5);

// Tabela z artikli
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, "Artikel", 1);
$pdf->Cell(30, 10, "Količina", 1);
$pdf->Cell(30, 10, "Cena", 1);
$pdf->Cell(30, 10, "Skupaj", 1);
$pdf->Ln();

$skupnaCena = 0;
$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
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

// Skupna cena
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, "Skupaj", 1);
$pdf->Cell(30, 10, number_format($skupnaCena, 2) . "€", 1);

$pdf->Output("I", "racun_" . $racunId . ".pdf");
exit;
