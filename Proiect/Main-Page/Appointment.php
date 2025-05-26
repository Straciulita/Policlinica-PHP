<?php
session_start();
include '../db.php';

// Filtrare și ordonare
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : '';

// Paginare
$perPage = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Căutare
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construiește WHERE și ORDER BY
$where = '';
if ($filter === 'decontat') {
    $where = 'WHERE Decontat = 1';
} elseif ($filter === 'nedecontat') {
    $where = 'WHERE Decontat = 0';
}
if ($search !== '') {
    $searchSql = "Denumire LIKE :search";
    if ($where === '') {
        $where = "WHERE $searchSql";
    } else {
        $where .= " AND $searchSql";
    }
}
$orderBy = '';
if ($order === 'pret_asc') {
    $orderBy = 'ORDER BY Pret ASC';
} elseif ($order === 'pret_desc') {
    $orderBy = 'ORDER BY Pret DESC';
}

// Numar total servicii (cu filtrare)
$countSql = "SELECT COUNT(*) FROM serviciiview $where";
$countStmt = $conn->prepare($countSql);
if ($search !== '') {
    $countStmt->bindValue(':search', '%' . $search . '%');
}
$countStmt->execute();
$totalServicii = $countStmt->fetchColumn();
$totalPages = ceil($totalServicii / $perPage);

// Preluare servicii pentru pagina curenta (cu filtrare și ordonare)
$sql = "SELECT Denumire, Descriere, Pret, Decontat FROM serviciiview $where $orderBy LIMIT $offset, $perPage";
$stmt = $conn->prepare($sql);
if ($search !== '') {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->execute();
$servicii = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $denumire = $_POST['add_to_cart'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!in_array($denumire, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $denumire;
    }
    header("Location: Appointment.php?page=" . (isset($_GET['page']) ? intval($_GET['page']) : 1));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicii - Sanavita</title>
    <link rel="stylesheet" href="style.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .servicii-container {
            max-width: 1100px;
            margin: 15rem auto 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 18px rgba(25,118,111,0.10);
            font-size: 1rem; /* font general mai mic */
        }
        .servicii-title {
            text-align: center;
            color: #19766f;
            margin-bottom: 2rem;
            font-size: 3rem; /* titlu mai mare */
            font-weight: bold;
        }
        .servicii-list {
            display: flex;
            flex-wrap: wrap;
            gap: 2.5rem;
            justify-content: center;
        }
        .serviciu-card {
            flex: 1 1 340px;
            min-width: 320px;
            max-width: 400px;
            background: #f7fafc;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(25,118,111,0.10);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 0.95rem; /* font mai mic pentru card */
            border-radius: 18px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .serviciu-card:hover {
            box-shadow: 0 6px 24px rgba(25,118,111,0.13);
            transform: translateY(-4px) scale(1.02);
        }
        .serviciu-denumire {
            font-size: 1.2rem;
            color: #19766f;
            font-weight: bold;
            margin-bottom: 0.7rem;
        }
        .serviciu-descriere {
            font-size: 1rem;
            color: #333;
            margin-bottom: 1rem;
        }
        .serviciu-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .serviciu-pret {
            font-size: 1rem;
            color: #145c57;
            font-weight: 600;
        }
        .serviciu-decontat {
            font-size: 1rem;
            color: #388e3c;
            background: #e0f7fa;
            border-radius: 5px;
            padding: 0.2rem 0.7rem;
        }
        .serviciu-nedecontat {
            font-size: 1rem;
            color: #b71c1c;
            background: #ffeaea;
            border-radius: 5px;
            padding: 0.2rem 0.7rem;
        }
        .btn-cos {
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
            margin-top: 1rem;
            align-self: flex-end;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            border-radius: 8px;
        }
        .btn-cos:hover, .btn-cos:focus {
            background: #145c57;
            outline: none;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 2.5rem 0 0 0;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            font-size: 1.2rem;
            color: #19766f;
            background: #e0f7fa;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .pagination a:hover {
            background: #19766f;
            color: #fff;
        }
        .pagination .active {
            background: #19766f;
            color: #fff;
            pointer-events: none;
        }
        .filtrare-bar {
            max-width: 1100px;
            margin: 2rem auto 0 auto;
            display: flex;
            gap: 2rem;
            align-items: center;
            justify-content: flex-end;
            font-size: 1.25rem;
        }
        .filtrare-bar select {
            font-size: 1.1rem;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            border: 1px solid #19766f;
            color: #19766f;
            background: #e0f7fa;
            margin-left: 0.5rem;
        }
        .filtrare-bar label {
            font-weight: 500;
            color: #19766f;
        }
        @media (max-width: 900px) {
            .servicii-list { flex-direction: column; align-items: center; }
        }
        .pagina-servicii-flex {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            max-width: 1400px;
            padding-top: 13rem; /* spațiu de sus */
            margin: 0 auto;
            gap: 3rem;
            min-height: 70vh;
        }
        .servicii-container {
            flex: 2;
            margin: 4rem 0 2rem 0;
        }
        .filtrare-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1.5rem;
            margin-top: 6rem;
            background: #e0f7fa;
            padding: 2rem 1.5rem;
            border-radius: 12px;
            min-width: 260px;
            max-width: 320px;
            font-size: 1.25rem;
            box-shadow: 0 2px 10px rgba(25,118,111,0.07);
        }
        @media (max-width: 1100px) {
            .pagina-servicii-flex { flex-direction: column; gap: 1.5rem; }
            .filtrare-bar { align-items: stretch; margin-top: 0; max-width: 100%; }
        }

        /* Stiluri pentru bara de filtrare modernizată */
        .filtrare-bar-modern {
            max-width: 1100px;
            margin: 2rem auto 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            background: #e0f7fa;
            padding: 2rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(25,118,111,0.07);
        }
        .filtru-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            width: 100%;
        }
        .filtru-group {
            flex: 1;
            min-width: 180px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .filtru-group label {
            margin: 0;
            color: #19766f;
            font-weight: 500;
        }
        .filtru-group select {
            flex: 1;
            font-size: 1rem;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            border: 1px solid #19766f;
            color: #19766f;
            background: #fff;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path fill="%2319766f" d="M5 7.5L2.5 5h5z"/></svg>'), linear-gradient(to bottom, #e0f7fa, #e0f7fa);
            background-repeat: no-repeat, repeat;
            background-position: right 0.7rem center, 0 0;
            background-size: 0.8rem auto, 100%;
        }
        .search-wrapper {
            position: relative;
            flex: 1;
        }
        .search-wrapper input {
            width: 100%;
            padding: 0.4rem 2.5rem 0.4rem 1rem;
            border-radius: 6px;
            border: 1px solid #19766f;
            font-size: 1rem;
            color: #333;
            background: #fff;
        }
        .search-wrapper button {
            position: absolute;
            top: 50%;
            left: 0.5rem;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #19766f;
            font-size: 1.2rem;
            cursor: pointer;
        }
        @media (max-width: 600px) {
            .filtru-row {
                flex-direction: column;
                align-items: stretch;
            }
            .filtru-group {
                width: 100%;
            }
        }

        /* Stiluri pentru bara de căutare modernizată */
        .search-bar-modern {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(25,118,111,0.10);
            padding: 1rem 1.2rem;
            max-width: 340px;
            min-width: 220px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .search-wrapper-modern {
            display: flex;
            align-items: center;
            width: 100%;
            background: #e0f7fa;
            border-radius: 8px;
            border: 1px solid #19766f;
            overflow: hidden;
            padding: 0.1rem 0.5rem;
        }
        .search-wrapper-modern input[type="text"] {
            border: none;
            background: transparent;
            padding: 0.4rem 0.7rem;
            font-size: 1rem;
            flex: 1;
            color: #19766f;
            min-width: 0;
            max-width: 140px;
            height: 2.2rem; /* asigură înălțime constantă */
            line-height: 2.2rem;
            display: flex;
            align-items: center;
        }
        .search-wrapper-modern button {
            background: #19766f;
            color: #fff;
            border: none;
            padding: 0.4rem 0.9rem;
            font-size: 1.1rem;
            border-radius: 6px;
            margin-left:15rem;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center; /* centrează iconița pe verticală */
            justify-content: center;
            height: 2.2rem; /* la fel ca inputul */
        }

        /* Ajustări stiluri */
        .servicii-container {
            font-size: 1.15rem; /* font general mai mare */
        }
        .serviciu-card {
            font-size: 1.1rem;
            padding: 2.3rem 1.7rem;
        }
        .serviciu-denumire {
            font-size: 1.35rem;
        }
        .serviciu-descriere {
            font-size: 1.1rem;
        }
        .serviciu-pret,
        .serviciu-decontat,
        .serviciu-nedecontat {
            font-size: 1.1rem;
        }
        .btn-cos {
            font-size: 1.1rem;
            padding: 0.7rem 1.5rem;
        }
        .filtrare-bar-modern,
        .search-bar-modern {
            font-size: 1.15rem;
        }
        .filtru-group select,
        .search-wrapper-modern input[type="text"] {
            font-size: 1.1rem;
            padding: 0.6rem 1.1rem;
        }
        .filtrare-bar-modern {
            gap: 2.2rem;
            padding: 2.3rem 1.7rem;
            border-radius: 16px;
        }
        .search-bar-modern {
            border-radius: 16px;
            padding: 1.2rem 1.5rem;
        }
        .search-wrapper-modern {
            border-radius: 10px;
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
                <li><a href="Appointment.php" class="active">Servicii</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="Istoric.php"><i class="fas fa-history"></i> Istoric</a></li>
                <?php else: ?>
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
    <div class="pagina-servicii-flex">
        <div>
            <!-- Bara de filtrare și ordonare -->
            <form class="filtrare-bar-modern" id="filtrareForm" method="get" action="Appointment.php">
                <div class="filtru-row">
                    <div class="filtru-group">
                        <label for="filter"><i class="fas fa-filter"></i></label>
                        <select name="filter" id="filter">
                            <option value="" <?php if ($filter == '') echo 'selected'; ?>>Toate</option>
                            <option value="decontat" <?php if ($filter == 'decontat') echo 'selected'; ?>>Decontate</option>
                            <option value="nedecontat" <?php if ($filter == 'nedecontat') echo 'selected'; ?>>Nedecontate</option>
                        </select>
                    </div>
                    <div class="filtru-group">
                        <label for="order"><i class="fas fa-sort-amount-down"></i></label>
                        <select name="order" id="order">
                            <option value="" <?php if ($order == '') echo 'selected'; ?>>Implicit</option>
                            <option value="pret_asc" <?php if ($order == 'pret_asc') echo 'selected'; ?>>Preț crescător</option>
                            <option value="pret_desc" <?php if ($order == 'pret_desc') echo 'selected'; ?>>Preț descrescător</option>
                        </select>
                    </div>
                </div>
                <?php if ($page > 1): ?>
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                <?php endif; ?>
            </form>
            <!-- Formular de căutare modernizat -->
            <form class="search-bar-modern" id="searchForm" method="get" action="Appointment.php" style="margin-top:1.5rem;">
                <div class="search-wrapper-modern">
                    <input type="text" name="search" placeholder="Caută serviciu..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" title="Caută"><i class="fas fa-search"></i></button>
                    <!-- Păstrează filtrul și ordonarea la search -->
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                </div>
            </form>
        </div>
        <div class="servicii-container">
            <div class="servicii-title">Serviciile Policlinicii Sanavita</div>
            <div class="servicii-list">
                <?php foreach ($servicii as $serv): ?>
                    <div class="serviciu-card">
                        <div class="serviciu-denumire"><?php echo htmlspecialchars($serv['Denumire']); ?></div>
                        <div class="serviciu-descriere"><?php echo htmlspecialchars($serv['Descriere']); ?></div>
                        <div class="serviciu-info">
                            <span class="serviciu-pret"><i class="fas fa-euro-sign"></i> <?php echo number_format($serv['Pret'], 2); ?> RON</span>
                            <?php if ($serv['Decontat']): ?>
                                <span class="serviciu-decontat"><i class="fas fa-check-circle"></i> Decontat</span>
                            <?php else: ?>
                                <span class="serviciu-nedecontat"><i class="fas fa-times-circle"></i> Nedecontat</span>
                            <?php endif; ?>
                        </div>
                        <!-- Formular pentru adăugarea în coș -->
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="add_to_cart" value="<?php echo htmlspecialchars($serv['Denumire']); ?>">
                            <button type="submit" class="btn-cos">
                                <i class="fas fa-cart-plus"></i> Adaugă în coș
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Paginare -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    // Păstrează filtrul și ordonarea la paginare
                    $queryBase = '';
                    if ($filter !== '') $queryBase .= '&filter=' . urlencode($filter);
                    if ($order !== '') $queryBase .= '&order=' . urlencode($order);
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1 . $queryBase; ?>">&laquo; Anterior</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i . $queryBase; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1 . $queryBase; ?>">Următor &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <footer class="footer">
        <p>&copy; 2025 Sanavita Clinic. All rights reserved.</p>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Submit la schimbare select
    document.querySelectorAll('.filtrare-bar-modern select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            document.getElementById('filtrareForm').submit();
        });
    });
    // Submit la search după 400ms de pauză la tastare
    let searchInput = document.querySelector('.search-bar-modern input[name="search"]');
    let timer;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(function() {
                document.getElementById('searchForm').submit();
            }, 400);
        });
    }
});
    </script>
</body>
</html>