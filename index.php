<?php 
    require_once __DIR__ . "/controller.php";
?>

<!DOCTYPE html>
<html lang="cs" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Dashboard</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-body">

<?php if (isset($_GET['success']) && isset($success_messages[$_GET['success']])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: <?= json_encode($success_messages[$_GET['success']]) ?>,
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    window.history.replaceState({}, document.title, window.location.pathname);
});
</script>
<?php endif; ?>

<?php if (isset($_GET['error']) && isset($error_messages[$_GET['error']])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: <?= json_encode($error_messages[$_GET['error']]) ?>,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    window.history.replaceState({}, document.title, window.location.pathname);
});
</script>
<?php endif; ?>

<?php if ($enable_login && !isset($_SESSION['logged_in'])): ?>

<div class="d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4">

            <h2 class="text-center mb-4">Přihlášení</h2>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Uživatelské jméno</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Heslo</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Přihlásit se
                </button>

            </form>

        </div>
    </div>
</div>

<?php else: ?>

<nav class="navbar navbar-expand-lg bg-body shadow-sm">
    <div class="container">

        <span class="navbar-brand fw-bold">
            Aurora Dashboard
        </span>

        <div class="d-flex align-items-center gap-3">

            <span id="online_badge" class="badge <?= $is_online ? 'text-bg-success' : 'text-bg-danger' ?>">
                <?= $is_online ? 'ONLINE' : 'OFFLINE' ?>
            </span>

            <button id="themeToggle" class="btn btn-outline-secondary btn-sm">
                Dark mode
            </button>


            <?php if ($enable_login) {
                echo '            <a href="?logout=1" class="btn btn-outline-danger btn-sm">
                Odhlásit se
            </a>';
            } ?>


        </div>

    </div>
</nav>

<div class="container py-4">

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Aktuální výkon</div>

                    <h2 class="mb-0">
                        <span id="output_power"><?= $is_online ? round($latest['output_power_w'] ?? 0) : 0 ?></span> W
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Celková výroba</div>

                    <h2 class="mb-0">
                        <span id="total_energy"><?= round($latest['total_energy_kwh'] ?? 0, 1) ?></span> kWh
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Frekvence sítě</div>

                    <h2 class="mb-0">
                        <span id="grid_frequency"><?= round($latest['grid_frequency_hz'] ?? 0, 2) ?></span> Hz
                    </h2>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mt-1">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Dnes vyrobeno</div>
                    <h3 class="mb-0"><?= $today_energy ?> kWh</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Tento měsíc</div>
                    <h3 class="mb-0"><?= $month_energy ?> kWh</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Peak výkon</div>
                    <h3 class="mb-0"><?= $peak_power ?> W</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Výdělek dnes</div>
                    <h3 class="mb-0"><?= number_format($estimated_money_today, 2, ',', ' ') ?> Kč</h3>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mt-1">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Inverter teplota</div>

                    <h3 class="mb-0">
                        <span id="inverter_temp"><?= round($latest['inverter_temp_c'] ?? 0, 1) ?></span> °C
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Booster teplota</div>

                    <h3 class="mb-0">
                        <span id="booster_temp"><?= round($latest['booster_temp_c'] ?? 0, 1) ?></span> °C
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5>Výkon za posledních 24 hodin</h5>
            <canvas id="powerChart"></canvas>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5>Teploty za posledních 24 hodin</h5>
            <canvas id="tempChart"></canvas>
        </div>
    </div>

    <div class="row g-4 mt-1">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5>Denní výroba za posledních 30 dní</h5>
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5>Měsíční výroba za posledních 12 měsíců</h5>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');

function setTheme(theme) {
    html.setAttribute('data-bs-theme', theme);
    localStorage.setItem('theme', theme);

    themeToggle.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
}

setTheme(localStorage.getItem('theme') || 'light');

themeToggle.addEventListener('click', () => {
    const current = html.getAttribute('data-bs-theme');
    setTheme(current === 'dark' ? 'light' : 'dark');
});
</script>

<script>
async function updateSolarData() {
    try {
        const response = await fetch('api-live.php');
        const json = await response.json();

        if (json.status !== 'ok') return;

        const d = json.data;

        document.getElementById('output_power').textContent =
            json.online ? Math.round(d.output_power_w ?? 0) : 0;

        document.getElementById('total_energy').textContent =
            Number(d.total_energy_kwh ?? 0).toFixed(1);

        document.getElementById('grid_frequency').textContent =
            Number(d.grid_frequency_hz ?? 0).toFixed(2);

        document.getElementById('inverter_temp').textContent =
            Number(d.inverter_temp_c ?? 0).toFixed(1);

        document.getElementById('booster_temp').textContent =
            Number(d.booster_temp_c ?? 0).toFixed(1);

        const badge = document.getElementById('online_badge');

        if (json.online) {
            badge.className = 'badge text-bg-success';
            badge.textContent = 'ONLINE';
        } else {
            badge.className = 'badge text-bg-danger';
            badge.textContent = 'OFFLINE';
        }

    } catch (e) {
        console.error(e);
    }
}

let powerChart = null;
let tempChart = null;

async function loadCharts() {
    try {
        const response = await fetch('api-history.php');
        const json = await response.json();

        if (json.status !== 'ok') return;

        const labels = json.data.map(row => row.created_at);
        const power = json.data.map(row => Number(row.output_power_w ?? 0));
        const inverterTemp = json.data.map(row => Number(row.inverter_temp_c ?? 0));
        const boosterTemp = json.data.map(row => Number(row.booster_temp_c ?? 0));

        if (!powerChart) {
            powerChart = new Chart(document.getElementById('powerChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Výkon (W)',
                        data: power,
                        tension: 0.3
                    }]
                },
                options: {
                    animation: false
                }
            });
        } else {
            powerChart.data.labels = labels;
            powerChart.data.datasets[0].data = power;
            powerChart.update('none');
        }

        if (!tempChart) {
            tempChart = new Chart(document.getElementById('tempChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Inverter (°C)',
                            data: inverterTemp,
                            tension: 0.3
                        },
                        {
                            label: 'Booster (°C)',
                            data: boosterTemp,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    animation: false
                }
            });
        } else {
            tempChart.data.labels = labels;
            tempChart.data.datasets[0].data = inverterTemp;
            tempChart.data.datasets[1].data = boosterTemp;
            tempChart.update('none');
        }
    } catch (e) {
        console.error(e);
    }
}

new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($daily_labels) ?>,
        datasets: [{
            label: 'Denní výroba (kWh)',
            data: <?= json_encode($daily_values, JSON_NUMERIC_CHECK) ?>
        }]
    },
    options: {
        animation: false
    }
});

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($monthly_labels) ?>,
        datasets: [{
            label: 'Měsíční výroba (kWh)',
            data: <?= json_encode($monthly_values, JSON_NUMERIC_CHECK) ?>
        }]
    },
    options: {
        animation: false
    }
});

updateSolarData();
loadCharts();

setInterval(updateSolarData, 5000);
setInterval(loadCharts, 30000);
</script>

<?php endif; ?>

</body>
</html>