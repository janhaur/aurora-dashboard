<?php
require_once "db.php";

header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT 
        created_at,
        output_power_w,
        inverter_temp_c,
        booster_temp_c
    FROM aurora_readings
    WHERE created_at >= NOW() - INTERVAL 24 HOUR
    ORDER BY created_at ASC
");

echo json_encode([
    'status' => 'ok',
    'data' => $stmt->fetchAll()
]);

?>