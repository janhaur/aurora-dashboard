<?php

require_once "db.php";
require_once "config.php";

header('Content-Type: application/json');

$valid_token = $api_token;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sent_token = $_POST['token'] ?? '';

    if ($sent_token !== $valid_token) {
        http_response_code(403);

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid token'
        ]);

        exit;
    }

    $serial                 = $_POST['serial'] ?? null;
    $total_energy_kwh       = $_POST['total_energy_kwh'] ?? null;
    $output_power_w         = $_POST['output_power_w'] ?? null;
    $grid_frequency_hz      = $_POST['grid_frequency_hz'] ?? null;
    $bulk_voltage_v         = $_POST['bulk_voltage_v'] ?? null;
    $inverter_temp_c        = $_POST['inverter_temp_c'] ?? null;
    $booster_temp_c         = $_POST['booster_temp_c'] ?? null;
    $is_online              = $_POST['is_online'] ?? 1;

    try {

        $stmt = $pdo->prepare("
            INSERT INTO aurora_readings (
                serial,
                total_energy_kwh,
                output_power_w,
                grid_frequency_hz,
                bulk_voltage_v,
                inverter_temp_c,
                booster_temp_c,
                is_online
            ) VALUES (
                :serial,
                :total_energy_kwh,
                :output_power_w,
                :grid_frequency_hz,
                :bulk_voltage_v,
                :inverter_temp_c,
                :booster_temp_c,
                :is_online
            )
        ");

        $stmt->execute([
            ':serial' => $serial,
            ':total_energy_kwh' => $total_energy_kwh,
            ':output_power_w' => $output_power_w,
            ':grid_frequency_hz' => $grid_frequency_hz,
            ':bulk_voltage_v' => $bulk_voltage_v,
            ':inverter_temp_c' => $inverter_temp_c,
            ':booster_temp_c' => $booster_temp_c,
            ':is_online' => $is_online
        ]);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Data ulozena'
        ]);

    } catch (PDOException $e) {

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}


try {

    $stmt = $pdo->query("
        SELECT *
        FROM aurora_readings
        ORDER BY id DESC
        LIMIT 1
    ");

    $data = $stmt->fetch();

    if (!$data) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Zadna data nenalezena!'
        ]);

        exit;
    }

    // OFFLINE kdyz jsou data starsi 2 minuty
    if (time() - strtotime($data['created_at']) > 120) {
        $data['is_online'] = 0;
    }

    echo json_encode([
        'status' => 'ok',
        'data' => $data
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}