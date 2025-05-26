<?php
include '../db.php';

$programari = [];

$sql = "SELECT Nume, Prenume, Ora, Data, Serviciu FROM vw_prog_detalii";
$stmt = $conn->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ora = substr($row['Ora'], 0, 5);
    $titlu = "$ora - {$row['Serviciu']} - {$row['Nume']} {$row['Prenume']}";

    // Culoare pe baza tipului de serviciu
    $culoare = match ($row['Serviciu']) {
        'Consultație' => '#007bff',
        'Control' => '#28a745',
        'Ecografie' => '#ffc107',
        default => '#6c757d',
    };

    $programari[] = [
        'title' => $titlu,
        'date' => $row['Data'],
        'color' => $culoare
    ];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Doctor - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' />
    <style>
        #calendar {
            max-width: 90%;
            margin: 0 auto;
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .legend {
            text-align: center;
            margin-top: 1rem;
        }
        .legend span {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 5px;
            margin: 0 0.5rem;
            color: white;
        }
        .legend .consultatie { background: #007bff; }
        .legend .control { background: #28a745; }
        .legend .eco { background: #ffc107; color: black; }
    </style>
</head>
<body>
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
        <h2 style="text-align:center; margin-top:2rem;">Calendar programări</h2>

        <input type="text" id="searchInput" placeholder="Caută pacient sau serviciu..." style="margin: 1rem auto; display:block; padding:0.5rem; width:50%; border-radius:6px; border:1px solid #ccc;">

        <div id='calendar'></div>

        <div class="legend">
            <span class="consultatie">Consultație</span>
            <span class="control">Control</span>
            <span class="eco">Ecografie</span>
        </div>
    </main>

    <!-- Modal -->
    <div id="eventModal" style="display:none; position:fixed; top:30%; left:50%; transform:translateX(-50%); background:#fff; padding:2rem; box-shadow:0 0 15px rgba(0,0,0,0.2); z-index:1000; border-radius:10px;">
        <span id="modalContent"></span>
        <br><br>
        <button onclick="document.getElementById('eventModal').style.display='none'">Închide</button>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        const programari = <?php echo json_encode($programari); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new window.FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ro',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: programari,
                dayMaxEvents: true,
                eventDisplay: 'block',
                eventClick: function(info) {
                    document.getElementById('modalContent').innerText = info.event.title;
                    document.getElementById('eventModal').style.display = 'block';
                },
                dayCellDidMount: function(info) {
                    const dateStr = info.date.toISOString().split('T')[0];
                    if (programari.some(ev => ev.date === dateStr)) {
                        info.el.classList.add('fc-day-has-event');
                    }
                }
            });

            calendar.render();

            // Filtrare după input
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const text = e.target.value.toLowerCase();
                calendar.removeAllEvents();
                const filtrate = programari.filter(ev => ev.title.toLowerCase().includes(text));
                filtrate.forEach(ev => calendar.addEvent(ev));
            });
        });
    </script>
</body>
</html>
