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
    <link rel="stylesheet" href="pacienti.css?v=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <a href="/Proiect/Main-Page/Main.php" class="logo">
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
                <h1>Lista Pacienților</h1>
                <form method="get" class="search-form">
                    <input type="text" id="search" name="search" placeholder="Caută pacient..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <label for="varsta_min">Vârstă minimă:</label>
                    <input type="number" id="varsta_min" name="varsta_min" min="0" value="<?php echo isset($_GET['varsta_min']) ? htmlspecialchars($_GET['varsta_min']) : ''; ?>">
                    <label for="varsta_max">Vârstă maximă:</label>
                    <input type="number" id="varsta_max" name="varsta_max" min="0" value="<?php echo isset($_GET['varsta_max']) ? htmlspecialchars($_GET['varsta_max']) : ''; ?>">
                    <label for="asigurat">Asigurat:</label>
                    <select id="asigurat" name="asigurat">
                        <option value="">Toți</option>
                        <option value="1" <?php echo (isset($_GET['asigurat']) && $_GET['asigurat'] === '1') ? 'selected' : ''; ?>>Da</option>
                        <option value="0" <?php echo (isset($_GET['asigurat']) && $_GET['asigurat'] === '0') ? 'selected' : ''; ?>>Nu</option>
                    </select>
                    <button type="submit"><i class='bx bx-search'></i></button>
                </form>
            </div>
            <script>
               // Filtrare live când scrii în câmpul search
document.getElementById("search").addEventListener("input", function () {
    filtreazaPacienti();
});

// Filtrare când se face submit pe formular (când apeși pe butonul de căutare)
document.querySelector('.search-form').addEventListener('submit', function(e) {
    e.preventDefault();  // previne refresh-ul paginii
    filtreazaPacienti();
});

// Funcție comună care face AJAX și actualizează tabelul
function filtreazaPacienti() {
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
}
            </script>

            <div class="actions">
                <button id="add-patient-btn" class="action-btn">Adaugă pacient nou</button>
            </div>
            <script>
                document.getElementById("add-patient-btn").addEventListener("click", function () {
                    document.getElementById("patient-modal").style.display = "block";
                    document.getElementById("modal-title").textContent = "Adaugă pacient";
                    document.getElementById("patient-form").reset();
                    document.getElementById("patient-id").value = '';
                });
            </script>

            <div id="tabel-pacienti">
                <?php
                
                afiseazaTabel("Pacient");
                ?>
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
    <script>
        document.getElementById("patient-form").addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('table', 'Pacient');

            fetch('save_record.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'succes') {
                    alert('Pacient salvat cu succes!');
                    closeModal('patient-modal');
                    location.reload();
                } else {
                    alert('Eroare: ' + data);
                }
            })
            .catch(error => {
                console.error('Eroare la salvare:', error);
            });
        });
    </script>

    <!-- Modal pentru editarea pacientului -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('edit-modal')">&times;</span>
            <h2>Modifică pacient</h2>
            <form id="edit-form">
                <label for="edit-nume">Nume:</label>
                <input type="text" id="edit-nume" name="nume" required>
                <label for="edit-prenume">Prenume:</label>
                <input type="text" id="edit-prenume" name="prenume" required>
                <label for="edit-telefon">Telefon:</label>
                <input type="tel" id="edit-telefon" name="telefon" pattern="^[0-9+\s()-]{7,15}$" placeholder="07xxxxxxxx" required>
                <label for="edit-varsta">Vârstă:</label>
                <input type="number" id="edit-varsta" name="varsta" min="0" required>
                <label for="edit-cnp">CNP:</label>
                <input type="text" id="edit-cnp" name="cnp" maxlength="13" pattern="\d{13}" required>
                <label for="edit-adresa">Adresă:</label>
                <input type="text" id="edit-adresa" name="adresa">
                <label for="edit-asigurat">Asigurat:</label>
                <select id="edit-asigurat" name="asigurat">
                    <option value="1">Da</option>
                    <option value="0">Nu</option>
                </select>
                <button type="button" id="update-btn">Update</button>
                <button type="button" onclick="closeModal('edit-modal')">Anulează</button>
                 <button type="button"  onclick="closeModal('edit-modal')" id="delete-btn" style="background-color: red; color: white;">Șterge</button>
            </form>
        </div>
    </div>
    <script>
        function editPatient(event, rowData) {
            event.stopPropagation();
            
            console.log(rowData); // <- adaugă aici să vezi ce conține
            document.getElementById('edit-nume').value = rowData.nume ;
            document.getElementById('edit-prenume').value = rowData.prenume;
            document.getElementById('edit-telefon').value = rowData.telefon;
            document.getElementById('edit-varsta').value = rowData.varsta ;
            document.getElementById('edit-cnp').value = rowData.cnp ;
            document.getElementById('edit-adresa').value = rowData.adresa ;
            document.getElementById('edit-asigurat').value = rowData.asigurat;
            document.getElementById('edit-modal').style.display = 'block';
        }

        document.getElementById("update-btn").addEventListener("click", function () {
            const formData = new FormData(document.getElementById("edit-form"));

            fetch("update_patient.php", {
                method: "POST",
                body: formData,
            })
            .then((response) => response.text())
            .then((data) => {
                if (data.trim() === "succes") {
                    alert("Pacient actualizat cu succes!");
                    closeModal("edit-modal");
                    location.reload();
                } else {
                    alert("Eroare: " + data);
                }
            })
            .catch((error) => {
                console.error("Eroare la actualizare:", error);
            });
        });
    </script>
    <script>
        document.getElementById("delete-btn").addEventListener("click", function () {
            // Deschide modalul de confirmare a ștergerii
            document.getElementById("delete-modal").style.display = "block";
        });
    </script>

    <!-- Modal pentru confirmarea ștergerii -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('delete-modal')">&times;</span>
            <h2>Confirmare ștergere</h2>
            <p>Sunteți sigur(ă) că doriți să ștergeți acest pacient?</p>
            <button id="confirm-delete-btn" onclick="deletePatient()">Șterge</button>
            <button onclick="closeModal('delete-modal')">Anulează</button>
        </div>
    </div>
    <script>
    let selectedRow = null;

    function openModal(row) {
        selectedRow = row;
        console.log("🔍 Modal deschis pentru rândul cu ID:", selectedRow?.getAttribute('data-id'));
        document.getElementById('delete-modal').style.display = 'block';
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', function () {
        if (selectedRow) {
            console.log("🗑️ Confirmat ștergerea pentru ID:", selectedRow.getAttribute('data-id'));
            deletePatient(selectedRow);
            closeModal('delete-modal');
        } else {
            console.warn("⚠️ Nicio linie selectată pentru ștergere.");
        }
    });

   function deletePatient() {
    const cnp = document.getElementById("edit-cnp").value; // Preia CNP-ul din câmpul edit-cnp

    if (!cnp) {
        alert("CNP-ul nu este valid!");
        return;
    }

    // Trimite cererea de ștergere către server
    fetch(`delete_record.php?cnp=${cnp}`, {
        method: "GET",
    })
    .then((response) => response.text())
    .then((data) => {
        if (data.trim() === "succes") {
            alert("Pacient șters cu succes!");
            closeModal("delete-modal");
            closeModal("edit-modal");
            location.reload(); // Reîncarcă tabelul
        } else {
            alert("Eroare la ștergere: " + data);
        }
    })
    .catch((error) => {
        console.error("Eroare la ștergere:", error);
    });
}

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            console.log("❌ Închid modalul:", modalId);
            modal.style.display = 'none';
        }
    }
</script>


    <script>
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <footer class="footer">
        <p>&copy; 2025 Policlinica Sanavita. Toate drepturile rezervate.</p>
    </footer>
</body>
</html>
