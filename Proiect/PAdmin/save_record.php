<?php
require_once 'functions.php';

// Crearea unei conexiuni PDO
try {
    $dsn = 'mysql:host=localhost;dbname=policlinica'; // Modifică această linie pentru a corespunde detaliilor tale de conexiune
    $username = 'root';
    $password = 'oracle';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Gestionarea erorilor
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Setează modul de recuperare al datelor
    ];

    // Conectarea la baza de date cu PDO
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    echo "Eroare de conexiune: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? null;
    $id = $_POST['id'] ?? null;
    
    // Salvăm toate datele, apoi eliminăm 'table' și 'id'
    $postData = $_POST;
    unset($postData['table'], $postData['id']);
    
    if (!$table) {
        echo "Lipsește parametrul 'table'.";
        exit;
    }

    if (empty($postData)) {
        echo "Nu au fost furnizate date pentru salvare.";
        exit;
    }
    
    // Verificăm dacă există câmpuri goale (cu excepția cazului în care sunt '0')
    $missingFields = [];
    foreach ($postData as $key => $value) {
        if ($value === '' || $value === null) {
            $missingFields[] = $key;
        }
    }

    if (!empty($missingFields)) {
        echo "Lipsesc valorile pentru câmpurile: " . implode(', ', $missingFields);
        exit;
    }

    // Apelăm funcția saveRecord
    echo saveRecord($conn, $table, $postData, $id);
}


?>
