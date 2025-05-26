<?php
require_once '../db.php';

// Funcție generală pentru afișarea unui tabel
function afiseazaTabelGeneral($numeTabel) {
    global $conn;

    try {
        // Verificăm dacă tabelul există
        $verifStmt = $conn->prepare("SHOW TABLES LIKE :tabel");
        $verifStmt->execute([':tabel' => $numeTabel]);

        if ($verifStmt->rowCount() === 0) {
            echo "<p style='color:red;'>Tabelul '$numeTabel' nu există.</p>";
            return;
        }

        // Selectăm toate datele
        $stmt = $conn->prepare("SELECT * FROM `$numeTabel`");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result && count($result) > 0) {
            echo "<div class='generic-table'><table>";
            echo "<tr>";
            $columns = array_keys($result[0]);
            foreach ($columns as $col) {
                echo "<th>" . htmlspecialchars($col) . "</th>";
            }
            echo "<th>Acțiuni</th>"; // coloană nouă pentru butoane
            echo "</tr>";

            foreach ($result as $row) {
                echo "<tr>";
                foreach ($row as $val) {
                    echo "<td>" . htmlspecialchars($val) . "</td>";
                }

                // Buton de editare — trimitem datele rândului ca JSON
                echo "<td><button class='edit-btn' onclick='editRow(event, " . json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) . ")'><i class='fas fa-edit'></i> Edit</button></td>";

                echo "</tr>";
            }

            echo "</table></div>";
        } else {
            echo "<p style='margin-top: 1rem;'>Tabelul '$numeTabel' este gol.</p>";
        }

    } catch (PDOException $e) {
        echo "<p style='color:red;'>Eroare la afișare: " . $e->getMessage() . "</p>";
    }
}

// Rulare doar dacă fișierul este accesat direct
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $tabelePermise = ['Pacient', 'Angajat', 'Programare', 'Consultatie', 'Medic'];

    if (isset($_GET['tabel'])) {
        $tabel = $_GET['tabel'];
        if (in_array($tabel, $tabelePermise)) {
            afiseazaTabelGeneral($tabel);
        } else {
            echo "<p style='color:red;'>Acces interzis la tabelul specificat.</p>";
        }
    } else {
        echo "<p style='color:orange;'>Specificați un tabel prin ?tabel=NumeTabel</p>";
    }
}
?>
