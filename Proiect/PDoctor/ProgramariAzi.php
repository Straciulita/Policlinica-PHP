<?php
include '../db.php';

// Data de azi
$azi = date('Y-m-d');

// Adăugare consultație (la submit formular)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idProgramare'])) {
    $idProgramare = $_POST['idProgramare'];
    $diagnostic = $_POST['diagnostic'];
    $tratament = $_POST['tratament'];

    $sql = "INSERT INTO Consultatie (idProgramare, diagnostic, tratament) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$idProgramare, $diagnostic, $tratament]);

    // Redirect după POST pentru a preveni dublarea la refresh
    $redirect = 'ProgramariAzi.php';
    if (isset($_GET['cnp'])) {
        $redirect .= '?cnp=' . urlencode($_GET['cnp']);
        $redirect .= '&succes=1';
    } else {
        $redirect .= '?succes=1';
    }
    header("Location: $redirect");
    exit;
}

// Selectăm programările de azi și CNP-ul pacientului + idProgramare
$sql = "SELECT p.idProgramare, pa.nume AS Nume, pa.prenume AS Prenume, p.ora AS Ora, s.Denumire AS Serviciu, pa.CNP
        FROM Programari p
        JOIN Pacient pa ON p.idPacient = pa.idPacient
        JOIN Servicii s ON p.idServiciu = s.idServiciu
        WHERE p.data = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$azi]);
$programariAzi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Istoric consultații pentru pacientul selectat (dacă există GET cnp)
$istoric = [];
$cnpSelectat = $_GET['cnp'] ?? '';
if ($cnpSelectat) {
    $sql = "SELECT c.diagnostic, c.tratament, p.data, s.Denumire AS Serviciu, pa.nume, pa.prenume
            FROM Consultatie c
            JOIN Programari p ON c.idProgramare = p.idProgramare
            JOIN Pacient pa ON p.idPacient = pa.idPacient
            JOIN Servicii s ON p.idServiciu = s.idServiciu
            WHERE pa.CNP = ?
            ORDER BY p.data DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$cnpSelectat]);
    $istoric = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Setează numele pacientului pentru afișare
    if (count($istoric) > 0) {
        $numePacientIstoric = $istoric[0]['nume'] . ' ' . $istoric[0]['prenume'];
    } else {
        // Dacă nu există consultații, extrage numele doar din Pacient
        $sql = "SELECT nume, prenume FROM Pacient WHERE CNP = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$cnpSelectat]);
        $pacient = $stmt->fetch(PDO::FETCH_ASSOC);
        $numePacientIstoric = $pacient ? $pacient['nume'] . ' ' . $pacient['prenume'] : '';
    }
} else {
    $numePacientIstoric = '';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Programări azi - Doctor</title>
    <link rel="stylesheet" href="style.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .main-flex {
            display: flex;
            gap: 2.5rem;
            align-items: flex-start;
            justify-content: center;
            flex-wrap: wrap;
        }
        .zona-stanga {
            flex: 1 1 400px;
            min-width: 340px;
        }
        .zona-dreapta {
            flex: 1 1 340px;
            min-width: 320px;
            max-width: 420px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(25, 118, 111, 0.10);
            padding: 2rem 1.5rem;
            margin-top: 2.5rem;
        }
        .istoric-title {
            color: #19766f;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .istoric-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .istoric-list li {
            margin-bottom: 1.2rem;
            border-bottom: 1px solid #e0f7fa;
            padding-bottom: 1rem;
        }
        .istoric-list li:last-child {
            border-bottom: none;
        }
        .istoric-data {
            color: #19766f;
            font-weight: bold;
        }
        .istoric-serviciu {
            color: #145c57;
            font-size: 1rem;
        }
        .istoric-diagnostic, .istoric-tratament {
            margin-top: 0.3rem;
        }
        .btn-consultatie {
            background: #19766f;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1.2rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(25, 118, 111, 0.08);
        }
        .btn-consultatie:hover, .btn-consultatie:focus {
            background: #145c57;
            box-shadow: 0 4px 16px rgba(25, 118, 111, 0.15);
            outline: none;
        }
        @media (max-width: 900px) {
            .main-flex { flex-direction: column; }
            .zona-dreapta { margin-top: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['succes'])): ?>
        <div id="notificareSucces" style="
            position:fixed; top:30px; left:50%; transform:translateX(-50%);
            background:#19766f; color:#fff; padding:1rem 2.5rem; border-radius:8px;
            box-shadow:0 2px 12px rgba(25,118,111,0.15); font-size:1.1rem; z-index:9999;">
            Consultația a fost salvată cu succes!
        </div>
        <script>
            setTimeout(function() {
                var notif = document.getElementById('notificareSucces');
                if (notif) notif.style.display = 'none';
            }, 2500);
        </script>
    <?php endif; ?>
    <header>
        <a href="../Main-Page/Main.php" class="logo">
            <img src="../Main-Page/Resurse/logo.png" alt="Policlinica Sanavita Logo" class="logo-img">
        </a>
        <span class="doctor-header-title">Panou Doctor</span>
        <div class="user-info">
            <i class="fas fa-user-md"></i>
            <span class="username">Dr. Doctor</span>
        </div>
    </header>
    <nav class="navbar">
        <ul>
            <li><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
            <li><a href="ProgramariAzi.php"><i class='bx bxs-calendar'></i> Programări azi</a></li>
            <li style="margin-left:auto;">
                <a href="../LoginForm/index.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
    <main>
        <div class="main-flex">
            <div class="zona-stanga">
                <h2 style="text-align:center; margin-top:2rem;">Programările de azi</h2>
                <div class="ora-data-curenta">
                    Data: <span id="dataCurenta"><?php echo date('d.m.Y'); ?></span> &nbsp; | &nbsp;
                    Ora: <span id="oraCurenta"><?php echo date('H:i:s'); ?></span>
                </div>
                <table class="tabel-programari">
                    <tr>
                        <th>Ora</th>
                        <th>Nume pacient</th>
                        <th>Serviciu</th>
                        <th>Acțiune</th>
                    </tr>
                    <?php if (count($programariAzi) > 0): ?>
                        <?php foreach ($programariAzi as $prog): ?>
                            <tr class="rand-pacient" data-cnp="<?php echo htmlspecialchars($prog['CNP']); ?>" data-id="<?php echo htmlspecialchars($prog['idProgramare']); ?>">
                                <td><?php echo htmlspecialchars(substr($prog['Ora'], 0, 5)); ?></td>
                                <td>
                                    <a href="ProgramariAzi.php?cnp=<?php echo urlencode($prog['CNP']); ?>" style="color:#19766f; text-decoration:underline;">
                                        <?php echo htmlspecialchars($prog['Nume'] . ' ' . $prog['Prenume']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($prog['Serviciu']); ?></td>
                                <td>
                                    <button class="btn-consultatie" data-id="<?php echo htmlspecialchars($prog['idProgramare']); ?>"
                                        data-nume="<?php echo htmlspecialchars($prog['Nume'] . ' ' . $prog['Prenume']); ?>">
                                        Adaugă consultație
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#888;">Nu există programări pentru azi.</td>
                        </tr>
                    <?php endif; ?>
                </table>
                <!-- Popup pentru adăugare consultație -->
                <div class="popup-bg" id="popupConsultatieBg" style="display:none;">
                    <div class="popup">
                        <span class="popup-close" onclick="closeConsultatiePopup()">&times;</span>
                        <h3>Adaugă consultație pentru <span id="popupNume"></span></h3>
                        <form method="post" class="popup-form">
                            <input type="hidden" name="idProgramare" id="inputIdProgramare">
                            <label for="diagnostic">Diagnostic:</label>
                            <textarea name="diagnostic" id="inputDiagnostic" required></textarea>
                            <label for="tratament">Tratament:</label>
                            <textarea name="tratament" id="inputTratament" required></textarea>
                            <button type="submit">Salvează</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="zona-dreapta">
                <div class="istoric-title">
                    Istoric consultații pacient
                    <?php if ($cnpSelectat && $numePacientIstoric): ?>
                        <br><span style="font-size:1rem; color:#145c57;">
                            <?php echo htmlspecialchars($numePacientIstoric); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <!-- În zona istoricului -->
                <?php if ($cnpSelectat && count($istoric) > 0): ?>
                    <form action="export_istoric_pdf.php" method="post" target="_blank" style="text-align:right;">
                        <input type="hidden" name="cnp" value="<?php echo htmlspecialchars($cnpSelectat); ?>">
                        <button type="submit" class="btn-consultatie" style="margin-bottom:1rem;">Exportă istoric PDF</button>
                    </form>
                <?php endif; ?>
                <ul class="istoric-list">
                    <?php if ($cnpSelectat && count($istoric) > 0): ?>
                        <?php foreach ($istoric as $item): ?>
                            <li>
                                <div class="istoric-data"><?php echo htmlspecialchars(date('d.m.Y', strtotime($item['data']))); ?></div>
                                <div class="istoric-serviciu"><?php echo htmlspecialchars($item['Serviciu']); ?></div>
                                <div class="istoric-diagnostic"><b>Diagnostic:</b> <?php echo htmlspecialchars($item['diagnostic']); ?></div>
                                <div class="istoric-tratament"><b>Tratament:</b> <?php echo htmlspecialchars($item['tratament']); ?></div>
                            </li>
                        <?php endforeach; ?>
                    <?php elseif ($cnpSelectat): ?>
                        <li><i>Nu există consultații pentru acest pacient.</i></li>
                    <?php else: ?>
                        <li><i>Selectează un pacient pentru a vedea istoricul consultațiilor.</i></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </main>
    <script>
        // Actualizare ora curentă la fiecare secundă
        setInterval(function() {
            var d = new Date();
            document.getElementById('oraCurenta').textContent =
                d.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('dataCurenta').textContent =
                d.toLocaleDateString('ro-RO');
        }, 1000);

        // Adaugă consultație
        document.querySelectorAll('.btn-consultatie').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.getElementById('inputIdProgramare').value = this.getAttribute('data-id');
                document.getElementById('popupNume').textContent = this.getAttribute('data-nume');
                document.getElementById('inputDiagnostic').value = '';
                document.getElementById('inputTratament').value = '';
                document.getElementById('popupConsultatieBg').style.display = 'flex';
            });
        });

        function closeConsultatiePopup() {
            document.getElementById('popupConsultatieBg').style.display = 'none';
        }
    </script>
</body>
</html>