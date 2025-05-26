<?php
session_start();
include '../db.php';

// Ștergere serviciu din coș
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $denumire = $_POST['remove'];
    if (($key = array_search($denumire, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: Cos.php");
    exit;
}

// Preluare detalii servicii din coș
$serviciiCos = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $sql = "SELECT Denumire, Pret, Decontat FROM serviciiview WHERE Denumire IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($_SESSION['cart']);
    $serviciiCos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($serviciiCos as $serv) {
        $total += $serv['Pret'];
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Coșul meu - Sanavita</title>
    <link rel="stylesheet" href="style.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .cos-container {
            max-width: 900px;
            margin: 15rem auto 2rem auto;
            padding: 2.5rem 2rem;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 18px rgba(25,118,111,0.10);
            font-size: 1.7rem; /* font general mai mare */
        }
        .cos-title {
            text-align: center;
            color: #19766f;
            margin-bottom: 2rem;
            font-size: 3rem; /* titlu mai mare */
            font-weight: bold;
        }
        .cos-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            
        }
        .cos-list th, .cos-list td {
            border-bottom: 1px solid #e0f7fa;
            padding: 1.3rem 1rem;
            text-align: left;
            font-size: 2rem; /* font mai mare pentru tabel */
        }
        .cos-list th {
            background: #e0f7fa;
            color: #19766f;
            font-size: 2rem;
        }
        .cos-list td {
            font-size: 1.5rem;
        }
        .btn-remove {
            background: #b71c1c;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 0.7rem 1.3rem;
            font-size: 1.3rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-remove:hover {
            background: #7a1010;
        }
        .cos-empty {
            text-align: center;
            color: #888;
            font-size: 1.7rem;
            margin: 2rem 0;
        }
        .cos-total {
            text-align: right;
            font-size: 2rem;
            font-weight: bold;
            color: #19766f;
            margin-top: 1.5rem;
        }
        .btn-continua {
            display: inline-block;
            background: #19766f;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 1rem 2.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1.5rem;
            text-decoration: none;
        }
        .btn-continua:hover {
            background: #145c57;
        }
    </style>
</head>
<body>
    <header>
        <a href="Main.php" class="logo">
            <img src="./Resurse/logo.png" alt="Policlinica Sanavita Logo" class="logo-img">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="Main.php">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="Appointment.php">Servicii</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <?php if (!isset($_SESSION['username'])): ?>
                    <li><a href="/Proiect/LoginForm/index.php">Login</a></li>
                    <li><a href="/Proiect/LoginForm/Singin.html">Register</a></li>
                <?php endif; ?>
                <li style="margin-left:auto;">
                    <a href="Cos.php" class="cart-btn" title="Coș de cumpărături" style="font-size:2.1rem; position:relative;">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <span style="position:absolute;top:-8px;right:-10px;background:#19766f;color:#fff;border-radius:50%;padding:2px 8px;font-size:1rem;">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="user-info">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="navbar-user"><i class="fas fa-user-circle"></i> Bun venit, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <div class="cos-container">
            <div class="cos-title"><i class="fas fa-shopping-cart"></i> Coșul meu</div>
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="cos-empty">
                    <i class="fas fa-info-circle"></i> Coșul este gol.
                </div>
            <?php else: ?>
                <table class="cos-list">
                    <tr>
                        <th>Serviciu</th>
                        <th>Preț</th>
                        <th>Decontat</th>
                        <th>Acțiune</th>
                    </tr>
                    <?php foreach ($serviciiCos as $serv): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($serv['Denumire']); ?></td>
                            <td><?php echo number_format($serv['Pret'], 2); ?> RON</td>
                            <td>
                                <?php if ($serv['Decontat']): ?>
                                    <span style="color:#388e3c;"><i class="fas fa-check-circle"></i> Da</span>
                                <?php else: ?>
                                    <span style="color:#b71c1c;"><i class="fas fa-times-circle"></i> Nu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="remove" value="<?php echo htmlspecialchars($serv['Denumire']); ?>">
                                    <button type="submit" class="btn-remove"><i class="fas fa-trash"></i> Șterge</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="cos-total">
                    Total: <?php echo number_format($total, 2); ?> RON
                </div>
                <div style="display: flex; gap: 1.5rem; margin-top: 2rem;">
                    <a href="Appointment.php" class="btn-continua"><i class="fas fa-arrow-left"></i> Înapoi la servicii</a>
                    <a href="ProgramareNoua.php" class="btn-continua" style="background:#388e3c;">
                        <i class="fas fa-calendar-check"></i> Programează-te
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer  style="position: fixed;"class="footer">
        <p>&copy; 2025 Sanavita Clinic. All rights reserved.</p>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>