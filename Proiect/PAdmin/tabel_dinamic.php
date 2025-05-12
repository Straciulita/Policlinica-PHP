<?php
require_once '../db.php'; // presupun că e calea corectă

// Conexiune PDO
function getConn() {
    try {
        $conn = new PDO('mysql:host=localhost;dbname=policlinica', 'root', 'oracle');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Conexiunea la baza de date a eșuat: " . $e->getMessage());
    }
}

function afiseazaTabel($numeTabel, $search = '', $varsta_min = null, $varsta_max = null, $asigurat = null) {
    $conn = getConn(); // Conectăm la baza de date

    // Construim interogarea
    $sql = "SELECT * FROM $numeTabel WHERE 1=1";

    // Filtrare după căutare
    if (!empty($search)) {
        $sql .= " AND (Nume LIKE :search 
                      OR Prenume LIKE :search 
                      OR CNP LIKE :search 
                      OR Telefon LIKE :search 
                      OR Adresa LIKE :search)";
    }

    // Filtrare după vârstă
    if (!empty($varsta_min)) {
        $sql .= " AND Varsta >= :varsta_min";
    }
    if (!empty($varsta_max)) {
        $sql .= " AND Varsta <= :varsta_max";
    }

    // Filtrare după statutul de asigurat
    if ($asigurat !== null && $asigurat !== '') {
        $sql .= " AND Asigurat = :asigurat";
    }

    $stmt = $conn->prepare($sql);

    // Legăm parametrii
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    if (!empty($varsta_min)) {
        $stmt->bindValue(':varsta_min', $varsta_min, PDO::PARAM_INT);
    }
    if (!empty($varsta_max)) {
        $stmt->bindValue(':varsta_max', $varsta_max, PDO::PARAM_INT);
    }
    if ($asigurat !== null && $asigurat !== '') {
        $stmt->bindValue(':asigurat', $asigurat, PDO::PARAM_INT);
    }

    // Executăm interogarea
    $stmt->execute();

    // Verificăm și afișăm rezultatele
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result && count($result) > 0) {
        echo "<table class='generic-table'>";
        echo "<tr>";
        // Afișăm capul tabelului (numele coloanelor)
        $columns = array_keys($result[0]); // Obținem cheile primului rând pentru capul tabelului
        foreach ($columns as $col) {
            echo "<th>" . htmlspecialchars($col) . "</th>";
        }
        echo "</tr>";

        // Afișăm rândurile
        foreach ($result as $row) {
            echo "<tr onclick='handleRowClick(this)' data-id='" . $row['idPacient'] . "'>";
            foreach ($row as $val) {
                echo "<td>" . htmlspecialchars($val) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='margin-top: 1rem;'>Niciun pacient găsit.</p>";
    }
}

// Răspuns pentru AJAX — doar dacă fișierul este accesat direct
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    if (isset($_GET['search']) || isset($_GET['varsta_min']) || isset($_GET['varsta_max']) || isset($_GET['asigurat'])) {
        afiseazaTabel("Pacient", 
            $_GET['search'] ?? '', 
            $_GET['varsta_min'] ?? null, 
            $_GET['varsta_max'] ?? null, 
            $_GET['asigurat'] ?? null
        );
        exit;
    }
}
?>
