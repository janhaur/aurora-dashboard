<?php
require_once "db.php";

header('Content-Type: application/json');

// poslední záznam -> status online/offline
$stmt = $pdo->query("
    SELECT created_at, is_online
    FROM aurora_readings
    ORDER BY id DESC
    LIMIT 1
");

$status_row = $stmt->fetch();

if (!$status_row) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No data'
    ]);
    exit;
}

$is_online = (
    (time() - strtotime($status_row['created_at'])) <= 120
    && (int)$status_row['is_online'] === 1
);

// poslední VALIDNÍ data
$stmt = $pdo->query("
    SELECT *
    FROM aurora_readings
    WHERE total_energy_kwh IS NOT NULL
    AND output_power_w IS NOT NULL
    ORDER BY id DESC
    LIMIT 1
");

$latest = $stmt->fetch();

if (!$latest) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No valid data'
    ]);
    exit;
}

// když je inverter offline -> výkon 0
if (!$is_online) {
    $latest['output_power_w'] = 0;
}

echo json_encode([
    'status' => 'ok',
    'online' => $is_online,
    'data' => $latest
]);