<?php
require_once '../db.php';

// Preluare date din GET pentru perioada selectată
$startDate = isset($_GET['start-date']) && $_GET['start-date'] ? $_GET['start-date'] : date('Y-01-01');
$endDate = isset($_GET['end-date']) && $_GET['end-date'] ? $_GET['end-date'] : date('Y-12-31');

// Grupare varsta pacienți
$ranges = [
    '0-18' => 'varsta BETWEEN 0 AND 18',
    '19-35' => 'varsta BETWEEN 19 AND 35',
    '36-60' => 'varsta BETWEEN 36 AND 60',
    '60+' => 'varsta > 60'
];
$labelsVarsta = array_keys($ranges);
$dataVarsta = [];
foreach ($ranges as $label => $conditie) {
    $sql = "SELECT COUNT(*) as cnt FROM Pacient WHERE $conditie";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $dataVarsta[] = (int)$row['cnt'];
}

// Frecvență programări pe luni pentru perioada selectată
$labelsLuni = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$dataLuni = array_fill(0, 12, 0);
$sql = "SELECT MONTH(data) as luna, COUNT(*) as cnt 
        FROM Programari 
        WHERE data BETWEEN :start AND :end 
        GROUP BY luna";
$stmt = $conn->prepare($sql);
$stmt->execute([':start' => $startDate, ':end' => $endDate]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $luna = (int)$row['luna'];
    if ($luna >= 1 && $luna <= 12) {
        $dataLuni[$luna - 1] = (int)$row['cnt'];
    }
}

// Venituri pe luni pentru perioada selectată
$dataVenit = array_fill(0, 12, 0);
$sql = "SELECT MONTH(p.data) as luna, SUM(s.Pret) as venit
        FROM Programari p
        JOIN Servicii s ON p.idServiciu = s.IdServiciu
        WHERE p.data BETWEEN :start AND :end
        GROUP BY luna";
$stmt = $conn->prepare($sql);
$stmt->execute([':start' => $startDate, ':end' => $endDate]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $luna = (int)$row['luna'];
    if ($luna >= 1 && $luna <= 12) {
        $dataVenit[$luna - 1] = (float)$row['venit'];
    }
}

// --- SUMAR RAPID: date reale ---
// Pacienți activi (total)
$sql = "SELECT COUNT(*) as cnt FROM Pacient";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$totalPacienti = (int)$row['cnt'];

// Angajați (total)
$sql = "SELECT COUNT(*) as cnt FROM Personal";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$totalAngajati = (int)$row['cnt'];

// Programări luna curentă
$currentMonth = date('m');
$currentYear = date('Y');
$sql = "SELECT COUNT(*) as cnt FROM Programari WHERE MONTH(data) = :luna AND YEAR(data) = :an";
$stmt = $conn->prepare($sql);
$stmt->execute([':luna' => $currentMonth, ':an' => $currentYear]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$programariLunaCurenta = (int)$row['cnt'];

// Venit total (suma tuturor programărilor)
$sql = "SELECT SUM(s.Pret) as venit FROM Programari p JOIN Servicii s ON p.idServiciu = s.IdServiciu";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$venitTotal = $row['venit'] ? number_format($row['venit'], 2, ',', '.') : '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Panel - Statistici</title>
    <link rel="stylesheet" href="style.css?v=1.0">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <!-- Sumar rapid -->
            <div class="summary-section" style="display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem; font-size: 1.5rem;">
                <div class="summary-box" style="flex:1; min-width: 180px; background: #eaefee; border-radius: 10px; box-shadow: 0 2px 8px rgba(25,118,111,0.08); padding: 1.5rem;">
                    <i class='bx bx-user' style="font-size:2.5rem; color:#19766f"></i>
                    <div>
                        <h4 style="margin:0; color:#19766f;">Pacienți activi</h4>
                        <p style="font-size:2rem; font-weight:700;"><?php echo $totalPacienti; ?></p>
                    </div>
                </div>
                <div class="summary-box" style="flex:1; min-width: 180px; background: #eaefee; border-radius: 10px; box-shadow: 0 2px 8px rgba(25,118,111,0.08); padding: 1.5rem;">
                    <i class='bx bx-id-card' style="font-size:2.5rem; color:#19766f"></i>
                    <div>
                        <h4 style="margin:0; color:#19766f;">Angajați</h4>
                        <p style="font-size:2rem; font-weight:700;"><?php echo $totalAngajati; ?></p>
                    </div>
                </div>
                <div class="summary-box" style="flex:1; min-width: 180px; background: #eaefee; border-radius: 10px; box-shadow: 0 2px 8px rgba(25,118,111,0.08); padding: 1.5rem;">
                    <i class='bx bx-calendar-check' style="font-size:2.5rem; color:#19766f"></i>
                    <div>
                        <h4 style="margin:0; color:#19766f;">Programări luna curentă</h4>
                        <p style="font-size:2rem; font-weight:700;"><?php echo $programariLunaCurenta; ?></p>
                    </div>
                </div>
                <div class="summary-box" style="flex:1; min-width: 180px; background: #eaefee; border-radius: 10px; box-shadow: 0 2px 8px rgba(25,118,111,0.08); padding: 1.5rem;">
                    <i class='bx bx-dollar-circle' style="font-size:2.5rem; color:#19766f"></i>
                    <div>
                        <h4 style="margin:0; color:#19766f;">Venit total</h4>
                        <p style="font-size:2rem; font-weight:700;"><?php echo $venitTotal; ?> lei</p>
                    </div>
                </div>
            </div>

            <!-- Zona pentru grafice principale -->
            <div class="charts-section" style="margin-top: 3rem;">
                <h2>Statistici vizuale</h2>
                <form id="chart-options" method="get" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                    <label for="chart-type">Tip grafic:</label>
                    <select id="chart-type" name="chart-type">
                        <option value="bar">Bar Chart</option>
                        <option value="pie">Pie Chart</option>
                    </select>

                    <label for="stat-type">Statistica:</label>
                    <select id="stat-type" name="stat-type">
                        <option value="varsta" <?php if(isset($_GET['stat-type']) && $_GET['stat-type']=='varsta') echo 'selected'; ?>>Grupe de vârstă pacienți</option>
                        <option value="frecventa" <?php if(isset($_GET['stat-type']) && $_GET['stat-type']=='frecventa') echo 'selected'; ?>>Frecvență programări pe luni</option>
                        <option value="venit" <?php if(isset($_GET['stat-type']) && $_GET['stat-type']=='venit') echo 'selected'; ?>>Venituri pe luni</option>
                    </select>

                    <label for="start-date">De la:</label>
                    <input type="date" id="start-date" name="start-date" value="<?php echo isset($_GET['start-date']) ? htmlspecialchars($_GET['start-date']) : ''; ?>">

                    <label for="end-date">Până la:</label>
                    <input type="date" id="end-date" name="end-date" value="<?php echo isset($_GET['end-date']) ? htmlspecialchars($_GET['end-date']) : ''; ?>">

                    <button type="submit" id="show-chart-btn">Afișează grafic</button>
                </form>
                <div style="width: 100%; max-width: 700px; margin: 2rem auto;">
                    <canvas id="dashboardChart"></canvas>
                </div>
            </div>
            <script>
      // Variabilele JS create din PHP
      const labelsVarsta = <?php echo json_encode($labelsVarsta); ?>;
      const dataVarsta = <?php echo json_encode($dataVarsta); ?>;
      const labelsLuni = <?php echo json_encode($labelsLuni); ?>;
      const dataLuni = <?php echo json_encode($dataLuni); ?>;
      const dataVenit = <?php echo json_encode($dataVenit); ?>;

      let chartInstance = null;
      function renderChart(type = 'bar', statType = 'varsta') {
          let labels = labelsVarsta;
          let data = dataVarsta;
          let label = 'Număr pacienți';
          if (statType === 'frecventa') {
              labels = labelsLuni;
              data = dataLuni;
              label = 'Număr programări';
          }
          if (statType === 'venit') {
              labels = labelsLuni;
              data = dataVenit;
              label = 'Venit (lei)';
          }
          const ctx = document.getElementById('dashboardChart').getContext('2d');
          if (chartInstance) chartInstance.destroy();
          chartInstance = new Chart(ctx, {
              type: type,
              data: {
                  labels: labels,
                  datasets: [{
                      label: label,
                      data: data,
                      backgroundColor: ['#19766f', '#5e928e', '#b2d8d8', '#eaefee', '#f4b942', '#f47c7c', '#a1c349', '#e2e2e2', '#b2b2b2', '#f4e285', '#f4a259', '#8cb369'],
                      borderWidth: 1
                  }]
              },
              options: {
                  responsive: true,
                  plugins: {
                      legend: { display: type === 'pie' }
                  },
                  scales: type === 'bar' ? { y: { beginAtZero: true } } : {}
              }
          });
      }
      // Detectează ce statistică să afișeze la încărcare
      let initialType = '<?php echo isset($_GET['chart-type']) ? $_GET['chart-type'] : 'bar'; ?>';
      let initialStat = '<?php echo isset($_GET['stat-type']) ? $_GET['stat-type'] : 'varsta'; ?>';
      renderChart(initialType, initialStat);
    </script>

            <!-- Secțiune Top Servicii -->
            <div class="charts-section" style="margin-top: 2rem;">
                <h2>Top 5 Servicii Populare</h2>
                <div style="width: 100%; max-width: 700px; margin: 2rem auto;">
                    <canvas id="topServiciiChart"></canvas>
                </div>
            </div>
            <script>
                // Date demo pentru top servicii
                const topServiciiData = {
                    labels: ['Consultatie', 'Analize', 'Ecografie', 'Vaccinare', 'Control'],
                    data: [60, 45, 30, 25, 20]
                };
                let topServiciiChart = null;
                function renderTopServiciiChart() {
                    const ctx = document.getElementById('topServiciiChart').getContext('2d');
                    if (topServiciiChart) topServiciiChart.destroy();
                    topServiciiChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: topServiciiData.labels,
                            datasets: [{
                                label: 'Număr servicii',
                                data: topServiciiData.data,
                                backgroundColor: ['#19766f', '#5e928e', '#b2d8d8', '#eaefee', '#f4b942'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
                renderTopServiciiChart();
            </script>

            <!-- Secțiune Distribuție Angajați -->
            <div class="charts-section" style="margin-top: 2rem;">
                <h2>Distribuție Angajați pe Funcții</h2>
                <div style="width: 100%; max-width: 700px; margin: 2rem auto;">
                    <canvas id="angajatiChart"></canvas>
                </div>
            </div>
            <script>
                // Date demo pentru distribuție angajați
                const angajatiData = {
                    labels: ['Medic', 'Asistent', 'Receptioner', 'Manager'],
                    data: [8, 7, 3, 2]
                };
                let angajatiChart = null;
                function renderAngajatiChart() {
                    const ctx = document.getElementById('angajatiChart').getContext('2d');
                    if (angajatiChart) angajatiChart.destroy();
                    angajatiChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: angajatiData.labels,
                            datasets: [{
                                label: 'Angajați',
                                data: angajatiData.data,
                                backgroundColor: ['#19766f', '#5e928e', '#b2d8d8', '#f4b942'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: true } }
                        }
                    });
                }
                renderAngajatiChart();
            </script>

   

    <footer class="footer">
        <p>&copy; 2025 Policlinica Sanavita. Toate drepturile rezervate.</p>
    </footer>
</body>
</html>