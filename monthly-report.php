<?php

require_once "config.php";
require_once "db.php";

$cron_token = $_GET['token'] ?? '';

if ($cron_token !== $api_token) {
    die("Neplatný token\n");
}

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



// celková výroba
$stmt = $pdo->query("
    SELECT total_energy_kwh, created_at
    FROM aurora_readings
    ORDER BY id DESC
    LIMIT 1
");

$latest = $stmt->fetch();

$total_energy = round($latest['total_energy_kwh'] ?? 0, 2);
$latest_update = $latest['created_at'] ?? 'neznámé';

// minulý měsíc
$stmt = $pdo->query("
    SELECT
        MIN(total_energy_kwh) AS start_energy,
        MAX(total_energy_kwh) AS end_energy,
        MAX(total_energy_kwh) - MIN(total_energy_kwh) AS produced_kwh,

        MAX(output_power_w) AS peak_power,
        AVG(output_power_w) AS avg_power,

        AVG(inverter_temp_c) AS avg_inverter_temp,
        AVG(booster_temp_c) AS avg_booster_temp,

        MAX(inverter_temp_c) AS peak_inverter_temp,
        MAX(booster_temp_c) AS peak_booster_temp

    FROM aurora_readings
    WHERE created_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
    AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
");

$data = $stmt->fetch();

$start_energy = round($data['start_energy'] ?? 0, 2);
$end_energy = round($data['end_energy'] ?? 0, 2);

$produced = round($data['produced_kwh'] ?? 0, 2);

$money = round($produced * $price_per_kwh, 2);

$peak_power = round($data['peak_power'] ?? 0);
$avg_power = round($data['avg_power'] ?? 0);

$avg_inverter_temp = round($data['avg_inverter_temp'] ?? 0, 1);
$avg_booster_temp = round($data['avg_booster_temp'] ?? 0, 1);

$peak_inverter_temp = round($data['peak_inverter_temp'] ?? 0, 1);
$peak_booster_temp = round($data['peak_booster_temp'] ?? 0, 1);

$last_month = date('m/Y', strtotime('-1 month'));

$html = "
<h2>FVE vyúčtování za {$last_month}</h2>

<h3>Celkový stav elektrárny</h3>

<table border='1' cellpadding='10' cellspacing='0'>
    <tr>
        <td><b>Celková výroba</b></td>
        <td>{$total_energy} kWh</td>
    </tr>

    <tr>
        <td><b>Poslední aktualizace</b></td>
        <td>{$latest_update}</td>
    </tr>
</table>

<br>

<h3>Statistiky za měsíc {$last_month}</h3>

<table border='1' cellpadding='10' cellspacing='0'>

    <tr>
        <td><b>Stav na začátku měsíce</b></td>
        <td>{$start_energy} kWh</td>
    </tr>

    <tr>
        <td><b>Stav na konci měsíce</b></td>
        <td>{$end_energy} kWh</td>
    </tr>

    <tr>
        <td><b>Vyrobeno za měsíc</b></td>
        <td><b>{$produced} kWh</b></td>
    </tr>

    <tr>
        <td><b>Cena za kWh</b></td>
        <td>{$price_per_kwh} Kč</td>
    </tr>

    <tr>
        <td><b>Výdělek</b></td>
        <td><b>{$money} Kč</b></td>
    </tr>

    <tr>
        <td><b>Peak výkon</b></td>
        <td>{$peak_power} W</td>
    </tr>

    <tr>
        <td><b>Průměrný výkon</b></td>
        <td>{$avg_power} W</td>
    </tr>

    <tr>
        <td><b>Průměrná teplota inverteru</b></td>
        <td>{$avg_inverter_temp} °C</td>
    </tr>

    <tr>
        <td><b>Peak teplota inverteru</b></td>
        <td>{$peak_inverter_temp} °C</td>
    </tr>

    <tr>
        <td><b>Průměrná teplota boosteru</b></td>
        <td>{$avg_booster_temp} °C</td>
    </tr>

    <tr>
        <td><b>Peak teplota boosteru</b></td>
        <td>{$peak_booster_temp} °C</td>
    </tr>

</table>

<br>

<p>Automaticky generováno Aurora Dashboardem.</p>
";

if (empty($report_emails) || !is_array($report_emails)) {
    die("Žádné emaily v config.php\n");
}

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;

    $mail->Username = $smtp_user;
    $mail->Password = $smtp_pass;

    $mail->Port = $smtp_port;

    $mail->CharSet = 'UTF-8';

    if ($smtp_port == 465) {

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    } else {

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->setFrom($smtp_from_email, $smtp_from_name);

    foreach ($report_emails as $email) {
        $mail->addAddress($email);
    }

    $mail->isHTML(true);

    $mail->Subject = "FVE vyúčtování za {$last_month}";

    $mail->Body = $html;

    $mail->AltBody = "
FVE vyúčtování za {$last_month}

CELKOVÝ STAV:
Celková výroba: {$total_energy} kWh
Poslední aktualizace: {$latest_update}

STATISTIKY ZA MĚSÍC {$last_month}:

Stav na začátku měsíce: {$start_energy} kWh
Stav na konci měsíce: {$end_energy} kWh

Vyrobeno za měsíc: {$produced} kWh

Cena za kWh: {$price_per_kwh} Kč
Výdělek: {$money} Kč

Peak výkon: {$peak_power} W
Průměrný výkon: {$avg_power} W

Průměrná teplota inverteru: {$avg_inverter_temp} °C
Peak teplota inverteru: {$peak_inverter_temp} °C

Průměrná teplota boosteru: {$avg_booster_temp} °C
Peak teplota boosteru: {$peak_booster_temp} °C
";

    $mail->send();

    echo "Email odeslán\n";

} catch (Exception $e) {

    echo "Chyba při odesílání: {$mail->ErrorInfo}\n";
}