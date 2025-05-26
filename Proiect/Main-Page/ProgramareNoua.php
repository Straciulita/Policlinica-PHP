<?php
session_start();
include '../db.php';

// Inițializare variabile
$nume = $prenume = $telefon = $cnp = $varsta = $adresa = $asigurat = '';
$idPacient = null;
$isLogged = isset($_SESSION['username']);
$success = $error = "";

// Dacă utilizatorul este logat, preia datele pacientului
if ($isLogged) {
    $stmt = $conn->prepare("SELECT ID FROM acces WHERE nume = ?");
    $stmt->execute([$_SESSION['username']]);
    $cont = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cont) {
        $stmt = $conn->prepare("SELECT idPacient FROM conturipacient WHERE idCont = ?");
        $stmt->execute([$cont['ID']]);
        $contPacient = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contPacient) {
            $idPacient = $contPacient['idPacient'];
            $stmt = $conn->prepare("SELECT * FROM pacient WHERE idPacient = ?");
            $stmt->execute([$idPacient]);
            $pacient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pacient) {
                $nume = $pacient['nume'];
                $prenume = $pacient['prenume'];
                $telefon = $pacient['telefon'];
                $cnp = $pacient['cnp'];
                $varsta = $pacient['varsta'];
                $adresa = $pacient['adresa'];
                $asigurat = $pacient['asigurat'];
            }
        }
    }
}

// Procesare programare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data_programare'], $_POST['ora_programare'], $_POST['idServiciu'])) {
    $data = $_POST['data_programare'];
    $ora = $_POST['ora_programare'];
    $idServiciu = intval($_POST['idServiciu']);

    if ($idPacient && $idServiciu && $data && $ora) {
        $stmt = $conn->prepare("INSERT INTO programari (idServiciu, idPacient, ora, data) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$idServiciu, $idPacient, $ora, $data])) {
            $success = "Programarea a fost realizată cu succes!";
        } else {
            $error = "Eroare la salvarea programării.";
        }
    } else {
        $error = "Date lipsă pentru programare.";
    }
}

// Preia serviciile din coș (sau poți schimba sursa după nevoie)
$servicii = [];
if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $sql = "SELECT Denumire, Pret, ID as idServiciu FROM serviciiview WHERE Denumire IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($_SESSION['cart']);
    $servicii = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Programare nouă</title>
    <link rel="stylesheet" href="style.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f7fafc; }
        .programare-flex {
            display: flex;
            gap: 2.5rem;
            max-width: 900px;
            margin: 8rem auto 2rem auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 18px rgba(25,118,111,0.10);
            padding: 2.5rem 2rem;
            font-size: 1.15rem;
        }
        .programare-stg, .programare-dr {
            flex: 1;
        }
        .programare-title {
            text-align: center;
            color: #19766f;
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.3rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            color: #19766f;
            margin-bottom: 0.4rem;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.7rem 1rem;
            border-radius: 7px;
            border: 1px solid #19766f;
            font-size: 1.1rem;
            background: #f7fafc;
        }
        .form-group input[readonly] {
            background: #e0f7fa;
            color: #888;
        }
        .btn-programare {
            background: #19766f;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 0.9rem 2.2rem;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1.2rem;
        }
        .btn-programare:hover {
            background: #145c57;
        }
        .alert-success {
            background: #e0f7fa;
            color: #19766f;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            text-align: center;
        }
        .alert-error {
            background: #ffeaea;
            color: #b71c1c;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="programare-title"><i class="fas fa-calendar-plus"></i> Programare nouă</div>
    <?php if ($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <div class="programare-flex">
        <!-- Stânga: Date pacient -->
        <div class="programare-stg">
            <div class="form-group">
                <label>Nume</label>
                <input type="text" value="<?php echo htmlspecialchars($nume); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Prenume</label>
                <input type="text" value="<?php echo htmlspecialchars($prenume); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Telefon</label>
                <input type="text" value="<?php echo htmlspecialchars($telefon); ?>" readonly>
            </div>
            <div class="form-group">
                <label>CNP</label>
                <input type="text" value="<?php echo htmlspecialchars($cnp); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Vârstă</label>
                <input type="number" value="<?php echo htmlspecialchars($varsta); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Adresă</label>
                <input type="text" value="<?php echo htmlspecialchars($adresa); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Asigurat?</label>
                <input type="text" value="<?php echo ($asigurat ? 'Da' : 'Nu'); ?>" readonly>
            </div>
        </div>
        <!-- Dreapta: Calendar și programare -->
        <div class="programare-dr">
            <form method="post" id="formProgramare">
                <div class="form-group">
                    <label for="idServiciu">Alege serviciul</label>
                    <select name="idServiciu" id="idServiciu" required>
                        <option value="">-- Selectează serviciul --</option>
                        <?php foreach ($servicii as $serv): ?>
                            <option value="<?php echo $serv['idServiciu']; ?>">
                                <?php echo htmlspecialchars($serv['Denumire']); ?> (<?php echo number_format($serv['Pret'],2); ?> RON)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="data_programare">Data programării</label>
                    <input type="date" id="data_programare" name="data_programare" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="ora_programare">Ora programării</label>
                    <input type="time" id="ora_programare" name="ora_programare" required>
                </div>
                <button type="submit" class="btn-programare"><i class="fas fa-calendar-check"></i> Programează</button>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('formProgramare').addEventListener('submit', function(e) {
        if(!confirm('Ești sigură că vrei să faci această programare?')) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>