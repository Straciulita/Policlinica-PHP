<?php
require_once('../fpdf/fpdf.php');
include '../db.php';

if (!class_exists('FPDF')) {
    die('FPDF nu este găsit! Verifică calea ../fpdf/fpdf.php');
}

if (!isset($_POST['cnp'])) {
    die('CNP lipsă.');
}

$cnp = $_POST['cnp'];

// Preluare date pacient
$sql = "SELECT nume, prenume FROM Pacient WHERE CNP = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$cnp]);
$pacient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pacient) {
    die('Pacient inexistent.');
}

// Preluare istoric consultații
$sql = "SELECT c.diagnostic, c.tratament, p.data, s.Denumire AS Serviciu
        FROM Consultatie c
        JOIN Programari p ON c.idProgramare = p.idProgramare
        JOIN Servicii s ON p.idServiciu = s.idServiciu
        JOIN Pacient pa ON p.idPacient = pa.idPacient
        WHERE pa.CNP = ?
        ORDER BY p.data DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$cnp]);
$istoric = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === PDF GENERARE ===
class PDFSanavita extends FPDF
{
    function Header()
    {
        $logoPath = '../Main-Page/Resurse/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 28);
        }
        $this->SetFont('DejaVuSans', '', 16);
        $this->Cell(0, 12, 'Policlinica Sanavita', 0, 1, 'C');
        $this->SetFont('DejaVuSans', '', 11);
        $this->Cell(0, 7, 'Adresa: Strada Gradina Veche NR 90, Galati 800552', 0, 1, 'C');
        $this->Cell(0, 7, 'Telefon: 0747 945 531', 0, 1, 'C');
        $this->Ln(2);
        $this->SetDrawColor(25, 118, 111);
        $this->SetLineWidth(0.7);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }
    function Footer()
    {
        $this->SetY(-18);
        $this->SetFont('DejaVuSans', '', 9);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 8, 'Policlinica Sanavita | www.sanavita.ro | Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDFSanavita();
$pdf->AddFont('DejaVuSans', '', 'DejaVuSans.php');
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->AddPage();

// Date pacient
$pdf->SetFont('DejaVuSans', '', 13);
$pdf->SetTextColor(25, 118, 111);
$pdf->Cell(0, 10, replaceDiacritics('Istoric consultații pacient'), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, replaceDiacritics('Nume: ' . $pacient['nume'] . ' ' . $pacient['prenume']), 0, 1);
$pdf->Cell(0, 8, replaceDiacritics('CNP: ' . $cnp), 0, 1);
$pdf->Cell(0, 8, replaceDiacritics('Data generare: ' . date('d.m.Y H:i')), 0, 1);
$pdf->Ln(4);

if (count($istoric) === 0) {
    $pdf->SetFont('DejaVuSans', '', 12);
    $pdf->Cell(0, 10, replaceDiacritics('Nu există consultații pentru acest pacient.'), 0, 1, 'C');
} else {
    foreach ($istoric as $item) {
        $pdf->SetFont('DejaVuSans', '', 11);
        $pdf->SetTextColor(25, 118, 111);
        $pdf->Cell(0, 8, replaceDiacritics('Data: ' . date('d.m.Y', strtotime($item['data']))), 0, 1);
        $pdf->SetFont('DejaVuSans', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 7, replaceDiacritics('Serviciu: ' . $item['Serviciu']), 0, 1);
        $pdf->SetFont('DejaVuSans', '', 11);
        $pdf->MultiCell(0, 7, replaceDiacritics('Diagnostic: ' . $item['diagnostic']));
        $pdf->MultiCell(0, 7, replaceDiacritics('Tratament: ' . $item['tratament']));
        $pdf->Ln(2);
        $pdf->SetDrawColor(200, 230, 230);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
    }
}

// Functie pentru inlocuirea diacriticelor cu echivalentul fara diacritice
function replaceDiacritics($string) {
    $diacritics = [
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
        'Ă' => 'A', 'Â' => 'A', 'Î' => 'I', 'Ș' => 'S', 'Ş' => 'S', 'Ț' => 'T', 'Ţ' => 'T'
    ];
    return strtr($string, $diacritics);
}

$pdf->Output('I', 'istoric_' . replaceDiacritics($pacient['nume']) . '_' . replaceDiacritics($pacient['prenume']) . '.pdf');
exit;