<?php
require_once 'tabel_general.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Angajați - Admin Panel</title>
    <link rel="stylesheet" href="style.css"> <!-- Legătura către fișierul CSS -->
    <link rel="stylesheet" href="pacienti.css">
    <link rel="stylesheet" href="angajati.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <!--Logo name-->
        <a href="/Proiect/Main-Page/Main.html" class="logo">
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
                <li><a href="index.html"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                <li><a href="Pacienti.php"><i class='bx bxs-user-detail'></i> Pacienți</a></li>
                <li><a href="Angajati.php"><i class='bx bxs-id-card'></i> Angajați</a></li>
                <li><a href="Servicii.php"><i class='bx bxs-calendar'></i> Servicii</a></li>
                <li><a href="Cabinete.html"><i class='bx bxs-clinic'></i> Cabinete</a></li>
                <li><a href="Rapoarte.html"><i class='bx bxs-report'></i> Rapoarte</a></li>
            </ul>
        </nav>
    </section>

    <section>
        <div class="content">
            <div class="search-header">
                <h1>Lista Angajaților</h1>
                <form method="get" class="search-form">
                    <input type="text" id="search" name="search" placeholder="Caută angajat..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

                    <label for="salariu_min">Salariu minim:</label>
                    <input type="number" id="salariu_min" name="salariu_min" step="0.01" value="<?php echo isset($_GET['salariu_min']) ? htmlspecialchars($_GET['salariu_min']) : ''; ?>">

                    <label for="salariu_max">Salariu maxim:</label>
                    <input type="number" id="salariu_max" name="salariu_max" step="0.01" value="<?php echo isset($_GET['salariu_max']) ? htmlspecialchars($_GET['salariu_max']) : ''; ?>">

                    <label for="functia">Funcția:</label>
                    <select id="functia" name="functia">
                        <option value="">Toate</option>
                        <option value="Medic Rezident" <?php echo (isset($_GET['functia']) && $_GET['functia'] === 'Medic Rezident') ? 'selected' : ''; ?>>Medic Rezident</option>
                        <option value="Asistent" <?php echo (isset($_GET['functia']) && $_GET['functia'] === 'Asistent') ? 'selected' : ''; ?>>Asistent</option>
                        <option value="Medic" <?php echo (isset($_GET['functia']) && $_GET['functia'] === 'Medic') ? 'selected' : ''; ?>>Medic</option>
                    </select>

                    <button type="submit"><i class='bx bx-search'></i></button>
                </form>
            </div>

            <div class="actions">
                <button id="add-employee-btn" class="action-btn">Adaugă angajat nou</button>
            </div>

            <div id="tabel-pacienti">
                <?php
                require_once 'tabel_dinamic.php';
                afiseazaTabelGeneral("Personal");
                ?>
            </div>
        </div>
    </section>

    <!-- Modal pentru adăugare/modificare angajat -->
    <div id="employee-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('employee-modal')">&times;</span>
            <h2 id="modal-title">Adaugă/Modifică angajat</h2>
            <form id="employee-form" method="POST" action="save_employee.php">
                <input type="hidden" id="employee-id" name="id">
                <label for="nume">Nume:</label>
                <input type="text" id="nume" name="nume" required>
                <label for="prenume">Prenume:</label>
                <input type="text" id="prenume" name="prenume" required>
                <label for="cnp">CNP:</label>
                <input type="text" id="cnp" name="cnp" maxlength="13" pattern="\d{13}" required>
                <label for="tel">Telefon:</label>
                <input type="tel" id="tel" name="tel" pattern="^[0-9+\s()-]{7,15}$" placeholder="07xxxxxxxx" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="functia">Funcția:</label>
                <input type="text" id="functia" name="functia" required>
                <label for="salariu">Salariu:</label>
                <input type="number" id="salariu" name="salariu" step="0.01" required>
                <label for="data_angajarii">Data angajării:</label>
                <input type="date" id="data_angajarii" name="data_angajarii" required>
                <label for="idCabinet">ID Cabinet:</label>
                <input type="number" id="idCabinet" name="idCabinet" required>
                <button type="submit">Salvează</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Policlinica Sanavita. Toate drepturile rezervate.</p>
    </footer>

    <script>
        document.getElementById("add-employee-btn").addEventListener("click", function () {
            document.getElementById("employee-modal").style.display = "block";
            document.getElementById("modal-title").textContent = "Adaugă angajat";
            document.getElementById("employee-form").reset();
            document.getElementById("employee-id").value = '';
        });

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
    </script>
</body>
</html>