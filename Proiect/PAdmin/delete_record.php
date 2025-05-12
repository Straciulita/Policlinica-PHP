<?php
// Include fișierul care conține clasa Database
require_once '../db.php';  // Asigură-te că calea este corectă

// Obține ID-ul pacientului din parametrii URL-ului
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id) {
        try {
            // Creează instanța clasei Database și obține conexiunea
            $db = new Database();
            $conn = $db->conn;  // Aceasta este conexiunea PDO

            // Creează SQL-ul pentru ștergere
            $sql = "DELETE FROM pacient WHERE idPacient = :id";

            // Pregătește statement-ul
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            // Execută statement-ul
            $stmt->execute();

            // Redirectează înapoi la pagina principală (sau altă locație)
            header('Location: Pacienti.php');
            exit();
        } catch (PDOException $e) {
            // În caz de eroare
            echo "Eroare: " . $e->getMessage();
        }
    } else {
        echo "ID invalid!";
    }
} else {
    echo "ID nu a fost specificat!";
}
?>
