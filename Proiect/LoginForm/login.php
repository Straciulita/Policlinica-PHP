<?php
require_once '../db.php'; // Clasa de conexiune DB

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->conn;

    // Preluăm datele din formular
    $username = $_POST['username'] ?? ''; // Folosește 'nume' pentru username
    $password = $_POST['password'] ?? ''; // Folosește 'parola' pentru password

    if (empty($username) || empty($password)) {
        echo "Te rugăm să completezi toate câmpurile.";
        exit();
    }

    // Verificăm dacă utilizatorul există
    $sql = "SELECT * FROM acces WHERE nume = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR); // Legăm parametrul :username

    try {
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificăm parola simplă (fără criptare)
            if ($password === $user['parola']) {
                $_SESSION['user'] = $user['nume'];

                // Redirecționare în funcție de rol
                if ($username === 'admin') {
                    header("Location: ../PAdmin/index.html");
                    exit();
                } else {
                    header("Location: ../Main-Page/Main.html");
                    exit();
                }
            } else {
                echo "Parolă incorectă!";
            }
        } else {
            echo "Utilizator inexistent!";
        }
    } catch (PDOException $e) {
        echo "Eroare de conectare la baza de date: " . $e->getMessage();
    }
} else {
    echo "Acces nepermis!";
}
?>
