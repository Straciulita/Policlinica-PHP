<?php
require_once '../db.php'; // Include conexiunea PDO

// Preluare date din POST
$nume     = $_POST['nume']     ?? '';
$prenume  = $_POST['prenume']  ?? '';
$telefon  = $_POST['telefon']  ?? '';
$varsta   = $_POST['varsta']   ?? 0;
$cnp      = $_POST['cnp']      ?? '';
$adresa   = $_POST['adresa']   ?? '';
$asigurat = isset($_POST['asigurat']) ? intval($_POST['asigurat']) : 0;

// Validare CNP
if (!preg_match('/^\d{13}$/', $cnp)) {
    echo "CNP invalid.";
    exit;
}

// Verificare dacă pacientul există
$stmt = $conn->prepare("SELECT idPacient FROM pacient WHERE cnp = ?");
$stmt->execute([$cnp]);
$pacient = $stmt->fetch();

if (!$pacient) {
    echo "Pacientul nu există.";
    exit;
}

// Actualizare pacient
$update = $conn->prepare("
    UPDATE pacient 
    SET nume = :nume,
        prenume = :prenume,
        telefon = :telefon,
        varsta = :varsta,
        adresa = :adresa,
        asigurat = :asigurat
    WHERE cnp = :cnp
");

try {
    $update->execute([
        ':nume'     => $nume,
        ':prenume'  => $prenume,
        ':telefon'  => $telefon,
        ':varsta'   => $varsta,
        ':adresa'   => $adresa,
        ':asigurat' => $asigurat,
        ':cnp'      => $cnp
    ]);
    echo "succes";
} catch (PDOException $e) {
    echo "Eroare la actualizare: " . $e->getMessage();
}
