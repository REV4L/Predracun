<?php
require_once 'baza.php';
require_once 'seja.php';
require_once 'tfpdf/tfpdf.php';
session_start();

if (!isset($_SESSION['racunId'])) {
    die("Račun ni določen.");
}

$racunId = $_SESSION['racunId'];
$ime = $_SESSION['ime'] ?? 'Neznano';
$priimek = $_SESSION['priimek'] ?? '';
$firma = "Moja Firma d.o.o.";
$datum = date('d.m.Y');

// Pridobi podatke o predračunu (vključno z imenom in priimkom kupca)
$queryPredracun = "SELECT st, ime_kupca, priimek_kupca, skupna_cena, koncna_cena FROM predracun WHERE id = ?";
$stmt = $link->prepare($queryPredracun);
$stmt->bind_param("i", $racunId);
$stmt->execute();
$predracunData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stevilka = $predracunData['st'];
$kup_ime = $predracunData['ime_kupca'];
$kup_priimek = $predracunData['priimek_kupca'];
$skupnaCena = floatval($predracunData['skupna_cena']);
$koncnaCena = floatval($predracunData['koncna_cena']);
$popust = ($skupnaCena > 0) ? round((($skupnaCena - $koncnaCena) / $skupnaCena) * 100, 2) : 0;

// Pridobi artikle
$queryArtikli = "SELECT a.ime, a.cena, r.kolicina
                 FROM artikel_predracun r
                 INNER JOIN artikli a ON a.id = r.artikel_id
                 WHERE r.predracun_id = ?";
$stmt = $link->prepare($queryArtikli);
$stmt->bind_param("i", $racunId);
$stmt->execute();
$result = $stmt->get_result();

// Generiraj PDF
$pdf = new tFPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu', '', 'DejaVuSans.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);

$pdf->SetFont('DejaVu', 'B', 16);
$pdf->Cell(0, 10, $firma, 0, 1);

$pdf->SetFont('DejaVu', '', 12);
$pdf->Cell(0, 10, "Predračun št.: " . $stevilka, 0, 1);
$pdf->Cell(0, 10, "Datum: " . $datum, 0, 1);
$pdf->Cell(0, 10, "Prodajalec: " . $ime . " " . $priimek, 0, 1);
$pdf->Cell(0, 10, "Kupec: " . $kup_ime . " " . $kup_priimek, 0, 1);
$pdf->Ln(5);

// Tabela artiklov
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(80, 10, "Artikel", 1);
$pdf->Cell(30, 10, "Količina", 1);
$pdf->Cell(30, 10, "Cena", 1);
$pdf->Cell(30, 10, "Skupaj", 1);
$pdf->Ln();

$pdf->SetFont('DejaVu', '', 12);
while ($row = $result->fetch_assoc()) {
    $artikel = $row['ime'];
    $kolicina = $row['kolicina'];
    $cena = $row['cena'];
    $skupaj = $kolicina * $cena;

    $pdf->Cell(80, 10, $artikel, 1);
    $pdf->Cell(30, 10, $kolicina, 1);
    $pdf->Cell(30, 10, number_format($cena, 2) . " €", 1);
    $pdf->Cell(30, 10, number_format($skupaj, 2) . " €", 1);
    $pdf->Ln();
}

// Skupaj, popust, končna cena
$pdf->SetFont('DejaVu', 'B', 12);
$pdf->Cell(140, 10, "Skupaj brez popusta", 1);
$pdf->Cell(30, 10, number_format($koncnaCena - $skupnaCena, 2) . " €", 1);
$pdf->Ln();

if ($popust > 0) {
    $pdf->Cell(140, 10, "Popust ({$popust}%)", 1);
    $pdf->Cell(30, 10, "-" . number_format($koncnaCena, 2) . " €", 1);
    $pdf->Ln();
}

$pdf->Cell(140, 10, "Končna cena", 1);
$pdf->Cell(30, 10, number_format($koncnaCena, 2) . " €", 1);

ob_clean();
$pdf->Output("D", "predracun-" . $stevilka . ".pdf");

exit();
