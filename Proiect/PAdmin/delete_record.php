<?php
require_once '../db.php';

if (isset($_GET['cnp'])) {
    $cnp = $_GET['cnp'];

    try {
        $stmt = $conn->prepare("DELETE FROM Pacient WHERE CNP = :cnp");
        $stmt->bindParam(':cnp', $cnp, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "succes";
        } else {
            echo "Eroare la ștergere.";
        }
    } catch (PDOException $e) {
        echo "Eroare: " . $e->getMessage();
    }
} else {
    echo "CNP-ul nu a fost furnizat.";
}
?>
