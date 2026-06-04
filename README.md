# Aurora Dashboard

![PHP](https://img.shields.io/badge/PHP-8+-blue)
![Python](https://img.shields.io/badge/Python-3-green)
![MySQL](https://img.shields.io/badge/MySQL-orange)
![License](https://img.shields.io/badge/license-MIT-green)

Web dashboard pro monitoring fotovoltaické elektrárny se střídačem Aurora.

Projekt sbírá provozní data ze střídače Aurora pomocí Python loggeru běžícího na Linux serveru.

Data jsou ukládána do MySQL databáze, odkud jsou dále zpracovávána a zobrazována ve webovém dashboardu vytvořeném v PHP.



## Ukázka

<img width="1915" height="899" alt="image" src="https://github.com/user-attachments/assets/33aa97b7-f4bd-4534-a636-85856251f1a2" />




## Funkce

* real-time monitoring výroby
* graf výkonu za posledních 24 hodin
* online/offline detekce střídače
* monitoring teplot
* měsíční statistiky výroby
* automatické email reporty
* REST API
* dark/light mode
* možnost zapnutí/vypnutí loginu
* responzivní dashboard

## Použitý hardware

* Aurora / Power-One střídač
* Komunikační USB kabel
* Linux server (Proxmox)

## Technologie

Backend

* PHP
* Python
* MySQL

Frontend

* HTML
* CSS
* JavaScript
* Chart.js
* Bootstrap
* SweetAlert2

Server

* Linux
* Nginx
* systemd
* Cron


## Python logger

* komunikuje se střídačem
* načítá data přes serial port
* odesílá data do API
* detekuje výpadky komunikace
* běží jako systemd service

Načítaná data:

* aktuální výkon
* celková výroba
* napětí
* frekvence sítě
* teploty


## Dashboard

Dashboard zobrazuje:

* aktuální výkon
* dnešní výrobu
* měsíční výrobu
* celkovou výrobu
* graf výkonu
* stav online/offline
* teploty zařízení


## Email reporty

Automaticky generované měsíční reporty obsahují:

* vyrobenou energii
* odhad výdělku
* peak výkon
* průměrný výkon
* statistiky teplot


## Instalace

1. Klonování projektu

```bash
git clone https://github.com/janhaur/aurora-dashboard.git
cd aurora-dashboard
```

***

2. Instalace PHP a web serveru

Projekt byl vyvíjen a testován na:

* PHP 8+
* MySQL
* Nginx

***

3. Vytvoření databáze

Vytvořte MySQL databázi.

Následně upravte přístupové údaje v souboru db.php.

***

4. Konfigurace projektu

Upravte soubory:

db.php

Nastavení připojení k databázi:

```php
$db_host = "localhost";
$db_name = "aurora";
$db_user = "user";
$db_pass = "password";
```

config.php

Nastavení:

* API tokenu
* SMTP serveru
* emailových reportů
* ceny za kWh

***

5. Instalace Python loggeru

Instalace Pythonu:
```bash
sudo apt install python3 python3-pip python3-venv
```
Vytvoření virtuálního prostředí:

```bash
cd aurora
python3 -m venv venv
source venv/bin/activate
```

Instalace závislostí:

```bash
pip install aurorapy
pip install requests
```

Nastavení sériového portu ve souboru:

```python
PORT = "/dev/serial/by-id/..."
```

***

6. Test loggeru

Ruční spuštění:

```bash
source venv/bin/activate
python3 aurora.py
```

Pokud je komunikace se střídačem funkční, měla by se začít ukládat data do databáze.

***

7. Systemd služba

Vytvoření služby:

```bash
sudo nano /etc/systemd/system/aurora.service
```

Ukázka:

```ini
[Unit]
Description=Aurora Solar Logger
After=network.target
[Service]
User=root
WorkingDirectory=/root/aurora
ExecStart=/root/aurora/venv/bin/python /root/aurora/aurora.py
Restart=always
RestartSec=10
[Install]
WantedBy=multi-user.target
```

Aktivace služby:

```bash
sudo systemctl enable aurora
sudo systemctl start aurora
```

Kontrola logů:

```bash
journalctl -u aurora -f
```

***

8. Cron úlohy

Pro automatické odesílání měsíčních reportů je potřeba nastavit cron.

Otevření crontabu:

```bash
crontab -e
```

Příklad spuštění každý první den v měsíci v 08:00:

```bash
0 8 1 * * curl "https://example.com/monthly-report.php?token=YOUR_TOKEN"
```

## Licence

MIT License
