from aurorapy.client import AuroraSerialClient, AuroraError
from datetime import datetime
import time
import requests
import os

PORT = ""
ADDRESS = 2

API_URL = "http://localhost/data.receive.php"
API_TOKEN = "secret_api_token"

MEASURES = {
    3: "output_power_w",
    4: "grid_frequency_hz",
    5: "bulk_voltage_v",
    21: "inverter_temp_c",
    22: "booster_temp_c",
}

def retry_read(label, func, retries=30, delay=0.5):
    for attempt in range(1, retries + 1):
        try:
            value = func()
            print(f"[OK] {label}: {value}")
            return value
        except AuroraError as e:
            print(f"[{attempt}/{retries}] {label}: Aurora/CRC chyba -> {e}")
        except Exception as e:
            print(f"[{attempt}/{retries}] {label}: chyba -> {e}")
        time.sleep(delay)

    print(f"[FAIL] {label}: nepodařilo se přečíst")
    return None

def send_to_api(data):
    try:
        response = requests.post(API_URL, data=data, timeout=10)
        print("API status:", response.status_code)
        print("API response:", response.text)
    except Exception as e:
        print("API ERROR:", e)

while True:
    print("\n========================================")
    print("Čas:", datetime.now())

    if not os.path.exists(PORT):
        print(f"Port {PORT} neexistuje, inverter offline.")
        time.sleep(60)
        continue

    results = {}

    try:
        client = AuroraSerialClient(
            port=PORT,
            address=ADDRESS,
            timeout=10
        )

        client.connect()
        print("Připojeno!")

        results["serial"] = retry_read("Serial", lambda: client.serial_number())

        results["total_energy_kwh"] = retry_read(
            "Total energy (kWh)",
            lambda: round(client.cumulated_energy(5) / 1000, 3)
        )

        for code, field_name in MEASURES.items():
            results[field_name] = retry_read(
                field_name,
                lambda c=code: client.measure(c)
            )

        results["is_online"] = 1
        results["token"] = API_TOKEN

    except Exception as e:
        print("CONNECT ERROR:", e)

    finally:
        try:
            client.close()
        except:
            pass

    print("\n=========== DATA ===========")
    for key, value in results.items():
        print(f"{key}: {value}")
    print("============================")

    if results.get("serial") and results.get("total_energy_kwh") is not None:
        send_to_api(results)
    else:
        print("Data nejsou kompletní, neposílám.")

    print("\nČekám 30 sekund...\n")
    time.sleep(30)