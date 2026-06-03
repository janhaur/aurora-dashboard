<?php
// DB připojení
$host = '';
$dbname = '';
$user = '';
$password = '';

try {

    date_default_timezone_set('Europe/Prague');

    $pdo = new PDO(

        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",

        $user,

        $password,

        [

            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES => false,

        ]

    );

    // automatický letní / zimní čas
    try {

        $pdo->exec("SET time_zone = 'Europe/Prague'");

    } catch (PDOException $e) {

        // fallback pokud hosting neumí timezone names
        $pdo->exec("SET time_zone = '+02:00'");
    }

    $pdo->exec("

        CREATE TABLE IF NOT EXISTS aurora_readings (

            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            serial VARCHAR(32) NULL,

            total_energy_kwh DOUBLE NULL,

            output_power_w DOUBLE NULL,

            grid_frequency_hz DOUBLE NULL,

            bulk_voltage_v DOUBLE NULL,

            inverter_temp_c DOUBLE NULL,

            booster_temp_c DOUBLE NULL,

            is_online TINYINT(1) NOT NULL DEFAULT 1,

            INDEX idx_created_at (created_at),

            INDEX idx_serial (serial)

        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    ");

} catch (PDOException $e) {

    die("DB error: " . $e->getMessage());

}

?>