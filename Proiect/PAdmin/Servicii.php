

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Servicii - Admin Panel</title>
    <link rel="stylesheet" href="style.css"> <!-- Legătura către fișierul CSS -->
    <link rel="stylesheet" href="pacienti.css">
    <link rel="stylesheet" href="servicii.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <!--Logo name-->
        <a href="/Proiect/Main-Page/Main.php" class="logo">
            <img src="/Proiect/Main-Page/Resurse/logo.png" alt="Policlinica Sanavita Logo" class="logo-img">
        </a>
        <!--User info-->
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span class="username">Admin</span>
        </div>
    </header>

    <!--Navigation Bar-->
    <section>
        <nav class="navbar">
            <h2 class="menu-title"><i class='bx bxs-cog'></i> Admin Dashboard</h2>
            <ul>
                <li><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                <li><a href="Pacienti.php"><i class='bx bxs-user-detail'></i> Pacienți</a></li>
                <li><a href="Angajati.php"><i class='bx bxs-id-card'></i> Angajați</a></li>
                <li><a href="Servicii.php"><i class='bx bxs-calendar'></i> Servicii</a></li>
                <li><a href="Cabinete.php"><i class='bx bxs-clinic'></i> Cabinete</a></li>
                <li>
                <a href="../LoginForm/index.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
                
            </ul>
        </nav>
    </section>

    <section>
        <div class="content">
            <div class="search-header">
                <h1>Lista Serviciilor</h1>
                <form method="get" class="search-form">
                    <input type="text" id="search" name="search" placeholder="Caută serviciu..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <label for="pret_min">Preț minim:</label>
                    <input type="number" id="pret_min" name="pret_min" step="0.01" value="<?php echo isset($_GET['pret_min']) ? htmlspecialchars($_GET['pret_min']) : ''; ?>">
                    <label for="pret_max">Preț maxim:</label>
                    <input type="number" id="pret_max" name="pret_max" step="0.01" value="<?php echo isset($_GET['pret_max']) ? htmlspecialchars($_GET['pret_max']) : ''; ?>">
                    <label for="decontat">Decontat:</label>
                    <select id="decontat" name="decontat">
                        <option value="">Toate</option>
                        <option value="1" <?php echo (isset($_GET['decontat']) && $_GET['decontat'] === '1') ? 'selected' : ''; ?>>Da</option>
                        <option value="0" <?php echo (isset($_GET['decontat']) && $_GET['decontat'] === '0') ? 'selected' : ''; ?>>Nu</option>
                    </select>
                    <button type="submit"><i class='bx bx-search'></i></button>
                </form>
            </div>

            <div class="actions">
                <button id="add-service-btn" class="action-btn">Adaugă serviciu nou</button>
            </div>

            <div id="tabel-pacienti">
                <?php
                require_once 'tabel_general.php';
                afiseazaTabelGeneral("Servicii");
                ?>
            </div>
        </div>
    </section>

    <!-- Modal pentru adăugare/modificare serviciu -->
    <div id="service-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('service-modal')">&times;</span>
            <h2 id="modal-title">Adaugă serviciu</h2>
            <form id="service-form" method="POST" action="save_service.php">
                <input type="hidden" id="service-id" name="id">
                <label for="denumire">Denumire:</label>
                <input type="text" id="denumire" name="denumire" required>
                <label for="descriere">Descriere:</label>
                <textarea id="descriere" name="descriere" rows="4"></textarea>
                <label for="pret">Preț:</label>
                <input type="number" id="pret" name="pret" step="0.01" required>
                <label for="decontat">Decontat:</label>
                <select id="decontat" name="decontat">
                    <option value="1">Da</option>
                    <option value="0">Nu</option>
                </select>
                <label for="idPersonal">ID Personal:</label>
                <input type="number" id="idPersonal" name="idPersonal">
                <button type="submit">Salvează</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Policlinica Sanavita. Toate drepturile rezervate.</p>
    </footer>

    <script>
        document.getElementById("add-service-btn").addEventListener("click", function () {
            document.getElementById("service-modal").style.display = "block";
            document.getElementById("modal-title").textContent = "Adaugă serviciu";
            document.getElementById("service-form").reset();
            document.getElementById("service-id").value = '';
        });

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
    </script>
</body>
</html>