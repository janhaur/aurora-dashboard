<?php
// Konfigurace pro Aurora Dashboard

//Vypnutí nebo Zapnutí přihlašování do dashboardu
$enable_login = false;

//Přihlašovací údaje (nejsou potřeba, pokud je $enable_login nastaveno na false)
$login_username = "admin";
$login_password = "admin123";

//Nastavení API tokenu pro zabezpečení API endpointů
$api_token = "secret_api_token";

// Cena za kWh v Kč pro výpočty úspor a zisků
$price_per_kwh = 10;


//Konfigurace emailu pro odesílání reportů
$smtp_host = "smtp.example.com";
$smtp_port = 465;

$smtp_user = "";
$smtp_pass = "";

$smtp_from_email = "";
$smtp_from_name = "Aurora Dashboard";

// Seznam emailů, na které budou odesílány reporty
$report_emails = [
    "email1@example.com",
    "email2@example.com"
];


?>