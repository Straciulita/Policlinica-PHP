<?php
require_once '../db.php';

function saveRecord($conn, $table, $data, $id = null) {
    // Extragem câmpurile și valorile din array
    $fields = array_keys($data);
    $values = array_values($data);

    if ($id) {
        // UPDATE
        $set = implode(", ", array_map(fn($f) => "$f = ?", $fields));
        $query = "UPDATE $table SET $set WHERE id = ?";
        // Adăugăm id-ul la finalul valorilor
        $values[] = $id;
    } else {
        // INSERT
        $columns = implode(", ", $fields);
        $placeholders = rtrim(str_repeat("?, ", count($fields)), ", ");
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    }

    // Pregătim interogarea PDO
    try {
        $stmt = $conn->prepare($query);
        // Executăm interogarea
        $stmt->execute($values);

        return "Succes";
    } catch (PDOException $e) {
        // Capturăm eroarea PDO și o returnăm
        return "Eroare la executarea interogării: " . $e->getMessage();
    }
}
?>
