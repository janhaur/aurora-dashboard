<?php

session_start();

require_once "config.php";

require_once "db.php";

if (isset($_GET['logout'])) {

    session_destroy();

    header("Location: ?success=logout");

    exit;

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = $_POST['username'] ?? '';

    $pass = $_POST['password'] ?? '';

    if ($user === $login_username && $pass === $login_password) {

        $_SESSION['logged_in'] = true;

        header("Location: ?success=login");

        exit;

    }

    header("Location: ?error=invalid_login");

    exit;

}

$success_messages = [

    'login' => 'Úspěšně přihlášen.',

    'logout' => 'Úspěšně odhlášen.',

];

$error_messages = [

    'invalid_login' => 'Špatné přihlašovací údaje.',

];

$latest = null;

$is_online = false;

$today_energy = 0;

$month_energy = 0;

$peak_power = 0;

$avg_power = 0;

$estimated_money_today = 0;

$daily_labels = [];

$daily_values = [];

$monthly_labels = [];

$monthly_values = [];

if (!$enable_login || isset($_SESSION['logged_in'])) {

    $stmt = $pdo->query("SELECT * FROM aurora_readings ORDER BY id DESC LIMIT 1");

    $latest = $stmt->fetch();

    if ($latest) {

        $is_online = (time() - strtotime($latest['created_at'])) <= 120;

    }

    $stmt = $pdo->query("

        SELECT MAX(total_energy_kwh) - MIN(total_energy_kwh) AS today_energy

        FROM aurora_readings

        WHERE DATE(created_at) = CURDATE()

    ");

    $today_energy = round(($stmt->fetch()['today_energy'] ?? 0), 2);

    $stmt = $pdo->query("

        SELECT MAX(total_energy_kwh) - MIN(total_energy_kwh) AS month_energy

        FROM aurora_readings

        WHERE MONTH(created_at) = MONTH(NOW())

        AND YEAR(created_at) = YEAR(NOW())

    ");

    $month_energy = round(($stmt->fetch()['month_energy'] ?? 0), 2);

    $stmt = $pdo->query("

        SELECT MAX(output_power_w) AS peak_power

        FROM aurora_readings

    ");

    $peak_power = round(($stmt->fetch()['peak_power'] ?? 0));

    $stmt = $pdo->query("

        SELECT AVG(output_power_w) AS avg_power

        FROM aurora_readings

        WHERE created_at >= NOW() - INTERVAL 24 HOUR

    ");

    $avg_power = round(($stmt->fetch()['avg_power'] ?? 0));

    $estimated_money_today = round($today_energy * $price_per_kwh, 2);

    $stmt = $pdo->query("

        SELECT 

            DATE(created_at) AS day,

            ROUND(MAX(total_energy_kwh) - MIN(total_energy_kwh), 2) AS energy

        FROM aurora_readings

        WHERE created_at >= NOW() - INTERVAL 30 DAY

        GROUP BY DATE(created_at)

        ORDER BY day ASC

    ");

    foreach ($stmt->fetchAll() as $row) {

        $daily_labels[] = $row['day'];

        $daily_values[] = (float) $row['energy'];

    }

    $stmt = $pdo->query("

        SELECT 

            DATE_FORMAT(created_at, '%Y-%m') AS month,

            ROUND(MAX(total_energy_kwh) - MIN(total_energy_kwh), 2) AS energy

        FROM aurora_readings

        WHERE created_at >= NOW() - INTERVAL 12 MONTH

        GROUP BY DATE_FORMAT(created_at, '%Y-%m')

        ORDER BY month ASC

    ");

    foreach ($stmt->fetchAll() as $row) {

        $monthly_labels[] = $row['month'];

        $monthly_values[] = (float) $row['energy'];

    }

}

?>