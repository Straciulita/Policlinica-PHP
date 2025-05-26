<?php
require_once 'tabel_general.php';
require_once '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cabinete - Admin Panel</title>
    <link rel="stylesheet" href="style.css"> <!-- Legătura către fișierul CSS -->
    <link rel="stylesheet" href="cabinet.css">
    <link rel="stylesheet" href="pacienti.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Header -->
    <header>
        <a href="/Proiect/Main-Page/Main.php" class="logo">
            <img src="/Proiect/Main-Page/Resurse/logo.png" alt="Policlinica Sanavita Logo" class="logo-img">
        </a>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span class="username">Admin</span>
        </div>
    </header>

    <!-- Navigation Bar -->
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

    <!-- Content Section -->
    <section>
        <div class="content">
            <!-- Search Header -->
            <div class="search-header">
                <h1>Lista Cabinetelor</h1>
                <form method="get" class="search-form">
                    <input type="text" id="search" name="search" placeholder="Caută cabinet..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"><i class='bx bx-search'></i></button>
                </form>
            </div>

            <!-- Actions -->
            <div class="actions">
                <button id="add-cabinet-btn" class="action-btn">Adaugă cabinet nou</button>
            </div>

            <!-- Tabel Cabinete -->
           <div id="tabel-pacienti">
                <?php
                require_once 'tabel_dinamic.php';
                afiseazaTabelGeneral("Cabinet");
                ?>
            </div>
        </div>
    </section>

  <!-- Modal pentru adăugare/modificare cabinet -->
<div id="cabinet-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('cabinet-modal')">&times;</span>
        <h2 id="modal-title">Adaugă cabinet</h2>
        <form id="cabinet-form" method="POST" action="save_cabinet.php">
            <input type="hidden" id="cabinet-id" name="id">

            <label for="denumire">Denumire:</label>
            <input type="text" id="denumire" name="denumire" required>

            <button type="submit">Salvează</button>
        </form>
    </div>
</div>
    <script>
    document.getElementById("cabinet-form").addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('table', 'Cabinet'); // opțional, dacă vrei să știi tabela în PHP

        fetch('save_record.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim().toLowerCase().includes('succes')) {
                alert('Cabinet salvat cu succes!');
                closeModal('cabinet-modal');
                location.reload();
            } else {
                alert('Eroare: ' + data);
            }
        })
        .catch(error => {
            console.error('Eroare la salvare:', error);
            alert('Eroare la salvare. Verifică consola pentru detalii.');
        });
    });
</script>


    <footer class="footer">
        <p>&copy; 2025 Policlinica Sanavita. Toate drepturile rezervate.</p>
    </footer>

    <script>
    document.getElementById('add-cabinet-btn').addEventListener('click', function () {
        document.getElementById('cabinet-form').reset(); // Resetează formularul
        document.getElementById('cabinet-id').value = ''; // Asigură-te că e gol pentru creare
        document.getElementById('cabinet-modal').style.display = 'block';
    });

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Închide modalul dacă dai click în afara lui
    window.onclick = function(event) {
        const modal = document.getElementById('cabinet-modal');
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>