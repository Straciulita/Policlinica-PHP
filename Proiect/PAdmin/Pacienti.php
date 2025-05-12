<?php
require_once 'tabel_dinamic.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacienți - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="pacienti.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    
</head>
<body>
    <header>
        <a href="/Proiect/Main-Page/Main.html" class="logo">
            <img src="/Proiect/Main-Page/Resurse/logo.png" alt="Policlinica Sanavita Logo" class="logo-img">
        </a>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span class="username">Admin</span>
        </div>
    </header>

    <section>
        <nav class="navbar">
            <h2 class="menu-title"><i class='bx bxs-cog'></i> Admin Dashboard</h2>
            <ul>
                <li><a href="index.html"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                <li><a href="Pacienti.php"><i class='bx bxs-user-detail'></i> Pacienți</a></li>
                <li><a href="Angajati.html"><i class='bx bxs-id-card'></i> Angajați</a></li>
                <li><a href="Programari.html"><i class='bx bxs-calendar'></i> Programări</a></li>
                <li><a href="Cabinete.html"><i class='bx bxs-clinic'></i> Cabinete</a></li>
                <li><a href="Rapoarte.html"><i class='bx bxs-report'></i> Rapoarte</a></li>
            </ul>
        </nav>
    </section>

    <section>
        <div class="content">
            <div class="search-header"> 
                <h1>Lista Pacienților</h1>
                <form method="get" class="search-form">
                    <input type="text" id="search" name="search" placeholder="Caută pacient..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <!-- Filtrare pe baza vârstei -->
                    <label for="varsta_min">Vârstă minimă:</label>
                    <input type="number" id="varsta_min" name="varsta_min" min="0" value="<?php echo isset($_GET['varsta_min']) ? htmlspecialchars($_GET['varsta_min']) : ''; ?>">
                    
                    <label for="varsta_max">Vârstă maximă:</label>
                    <input type="number" id="varsta_max" name="varsta_max" min="0" value="<?php echo isset($_GET['varsta_max']) ? htmlspecialchars($_GET['varsta_max']) : ''; ?>">
                    
                    <!-- Filtrare pe baza statutului de asigurat -->
                    <label for="asigurat">Asigurat:</label>
                    <select id="asigurat" name="asigurat">
                        <option value="">Toți</option>
                        <option value="1" <?php echo (isset($_GET['asigurat']) && $_GET['asigurat'] === '1') ? 'selected' : ''; ?>>Da</option>
                        <option value="0" <?php echo (isset($_GET['asigurat']) && $_GET['asigurat'] === '0') ? 'selected' : ''; ?>>Nu</option>
                    </select>
                    
                    <button type="submit"><i class='bx bx-search'></i></button>
                </form>
            </div>
            
            <!-- Buton pentru adăugarea unui pacient nou -->
            <div class="actions">
                <button id="add-patient-btn" class="action-btn">Adaugă pacient nou</button>
            </div>
            
            <div id="tabel-pacienti">
                <?php afiseazaTabel("Pacient"); ?>
            </div>
        </div>
    </section>

<!-- Modal pentru adăugare/modificare pacient -->
<div id="patient-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('patient-modal')">&times;</span>
        <h2 id="modal-title">Adaugă/Modifică pacient</h2>
        <form id="patient-form" method="POST" action="save_record.php">
            <input type="hidden" id="patient-id" name="id">

            <label for="nume">Nume:</label>
            <input type="text" id="nume" name="nume" required>

            <label for="prenume">Prenume:</label>
            <input type="text" id="prenume" name="prenume" required>

            <label for="telefon">Telefon:</label>
            <input type="tel" id="telefon" name="telefon" pattern="^[0-9+\s()-]{7,15}$" placeholder="07xxxxxxxx" required>

            <label for="varsta">Vârstă:</label>
            <input type="number" id="varsta" name="varsta" min="0" required>

            <label for="cnp">CNP:</label>
            <input type="text" id="cnp" name="cnp" maxlength="13" pattern="\d{13}" required>

            <label for="adresa">Adresă:</label>
            <input type="text" id="adresa" name="adresa">

            <label for="asigurat">Asigurat:</label>
            <select id="asigurat" name="asigurat">
                <option value="1">Da</option>
                <option value="0">Nu</option>
            </select>

            <button type="submit">Salvează</button>
        </form>
        <div id="form-feedback" style="margin-top: 10px; color: red;"></div>
    </div>
</div>

<!-- JavaScript pentru validare -->
<script>
document.getElementById("patient-form").addEventListener("submit", function (e) {
    const cnp = document.getElementById("cnp").value.trim();
    const telefon = document.getElementById("telefon").value.trim();
    const feedback = document.getElementById("form-feedback");

    let errors = [];

    if (!/^\d{13}$/.test(cnp)) {
        errors.push("CNP-ul trebuie să conțină exact 13 cifre.");
    }

    if (!/^[0-9+\s()-]{7,15}$/.test(telefon)) {
        errors.push("Telefonul nu este valid. Ex: 07xxxxxxxx");
    }

    if (errors.length > 0) {
        e.preventDefault(); // Oprește trimiterea formularului
        feedback.innerHTML = errors.join("<br>");
    } else {
        feedback.innerHTML = "";
    }
});
</script>


    <!-- Modal pentru confirmarea ștergerii -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('delete-modal')">&times;</span>
            <h2>Confirmare ștergere</h2>
            <p>Sunteți sigur(ă) că doriți să ștergeți acest pacient?</p>
            <button id="confirm-delete-btn">Șterge</button>
            <button onclick="closeModal('delete-modal')">Anulează</button>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Policlinica Sanavita. Toate drepturile rezervate.</p>
    </footer>

    <script>
         document.getElementById("search").addEventListener("input", function () {
    const query = this.value;
    const varstaMin = document.getElementById("varsta_min").value;
    const varstaMax = document.getElementById("varsta_max").value;
    const asigurat = document.getElementById("asigurat").value;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "tabel_dinamic.php?search=" + encodeURIComponent(query) +
        "&varsta_min=" + encodeURIComponent(varstaMin) +
        "&varsta_max=" + encodeURIComponent(varstaMax) +
        "&asigurat=" + encodeURIComponent(asigurat), true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("tabel-pacienti").innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});

// În cazul în care utilizatorul schimbă filtrele și apasă butonul de căutare
document.querySelector(".search-form").addEventListener("submit", function (e) {
    e.preventDefault();  // Previne comportamentul default de submit al formularului

    const query = document.getElementById("search").value;
    const varstaMin = document.getElementById("varsta_min").value;
    const varstaMax = document.getElementById("varsta_max").value;
    const asigurat = document.getElementById("asigurat").value;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "tabel_dinamic.php?search=" + encodeURIComponent(query) +
        "&varsta_min=" + encodeURIComponent(varstaMin) +
        "&varsta_max=" + encodeURIComponent(varstaMax) +
        "&asigurat=" + encodeURIComponent(asigurat), true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("tabel-pacienti").innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});


document.getElementById('patient-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Oprește trimiterea standard

    const formData = new FormData(this);
    formData.append('table', 'Pacient'); // Numele exact al tabelului

   fetch('save_record.php', {
    method: 'POST',
    body: formData
})
.then(response => response.text())
.then(data => {
    if (data.trim() === 'succes') {
        alert('Pacient salvat cu succes!');
        closeModal('patient-modal');
        // Poți reîncărca tabela de pacienți aici, dacă vrei
    } else {
        alert('Eroare: ' + data);
    }
})
.catch(error => {
    console.error('Eroare la salvare:', error);
});

});


        // Deschiderea modalului de adăugare pacient
        document.getElementById("add-patient-btn").addEventListener("click", function () {
            document.getElementById("patient-modal").style.display = "block";
            document.getElementById("modal-title").textContent = "Adaugă pacient";
            document.getElementById("patient-form").reset();  // Resetare formular
            document.getElementById("patient-id").value = ''; // Asigură-te că ID-ul e gol
        });

        // Închiderea modalului
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
let selectedRow = null;  // Variabila pentru a stoca rândul selectat

// Funcția care se apelează atunci când se face click pe un rând
function handleRowClick(row) {
    // Obținem ID-ul pacientului din atributul data-id
    var rowId = row.getAttribute('data-id'); 
    if (rowId) {
        console.log('ID Pacient:', rowId);
        selectedRow = row;  // Salvează rândul selectat
        openModal();  // Deschide modalul de confirmare
    } else {
        console.log('ID-ul nu a fost găsit.');
    }
}

// Funcția pentru deschiderea modalului
function openModal() {
    // Afișează modalul
    document.getElementById('delete-modal').style.display = 'block';
}

// Funcția pentru închiderea modalului
function closeModal(modalId) {
    // Închide modalul
    document.getElementById(modalId).style.display = 'none';
}

// Confirmarea ștergerii din modal
document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    if (selectedRow) {
        // Trimite cererea pentru ștergerea pacientului printr-un link
        deletePatient(selectedRow);
        closeModal('delete-modal');  // Închide modalul
    }
});

// Funcția de ștergere a pacientului
function deletePatient(row) {
    // Obținem ID-ul pacientului
    var patientId = row.getAttribute('data-id');

    // Construieste URL-ul pentru a trimite cererea de ștergere
    const deleteUrl = `delete_record.php?id=${patientId}`;

    // Redirectează utilizatorul către URL-ul de ștergere
    window.location.href = deleteUrl;

    // Șterge rândul din DOM (după ce utilizatorul confirmă)
    row.remove();
}

// Atașează evenimentul de click pe fiecare rând al tabelului
document.querySelectorAll('tr').forEach(row => {
    row.addEventListener('click', handleRowClick);
});


    </script>
</body>
</html>
