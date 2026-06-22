# Odevzdávač

Webová aplikace pro odevzdávání prací přes formulář. Vyplníš údaje, nahraješ soubory a odešleš — příjemci přijde email s přílohami. Volitelně se soubory nahrají i na Google Disk.

## Jak spustit

**1. Nainstaluj PHP**

Stáhni PHP z [windows.php.net/download](https://windows.php.net/download/) a přidej ho do PATH, nebo použij Laragon/XAMPP.

**2. Nastav config**

Zkopíruj `src/config.example.php` jako `src/config.php` a vyplň:

- `brevo_api_key` — API klíč z [Brevo](https://app.brevo.com) (free, 300 emailů/den). Po registraci ho najdeš v **Profile & Plan → SMTP & API → API Keys**.
- `from_address` — tvůj ověřený odesílatel v Brevo (Senders & Domains).

**3. Spusť server**

```
php -S localhost:8000 -t public -d upload_max_filesize=20M -d post_max_size=25M
```

**4. Otevři v prohlížeči**

```
http://localhost:8000
```

---

## Google Disk (volitelné)

Pokud chceš aby se soubory automaticky nahrávaly na Google Disk:

1. Vytvoř projekt na [console.cloud.google.com](https://console.cloud.google.com) a zapni **Google Drive API**
2. Vytvoř Service Account → stáhni JSON klíč → ulož ho jako `credentials.json` do kořene projektu
3. Na Google Disku vytvoř **Sdílenou složku** (Shared Drive) a přidej do ní email service accountu (z `credentials.json`, pole `client_email`) s rolí Přispěvatel
4. Zkopíruj ID složky z URL a dej ho do `src/config.php`:

```php
'drive' => [
    'folder_id'        => 'id-tve-sdilene-slozky',
    'credentials_json' => file_get_contents(__DIR__ . '/../credentials.json'),
],
```

---

## Hosting (Railway)

1. Nahraj projekt na GitHub
2. Na [railway.app](https://railway.app) vytvoř nový projekt z GitHub repozitáře
3. Nastav environment variables:

| Proměnná | Hodnota |
|---|---|
| `BREVO_API_KEY` | API klíč z Brevo |
| `MAIL_FROM_ADDRESS` | ověřený odesílatel |
| `MAIL_FROM_NAME` | jméno odesílatele |
| `DRIVE_FOLDER_ID` | ID Sdílené složky na Google Disku |
| `GOOGLE_CREDENTIALS_JSON` | celý obsah `credentials.json` |

4. Railway nasadí aplikaci automaticky při každém `git push`
