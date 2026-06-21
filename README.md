# Odevzdávač

Webová aplikace pro odevzdávání prací přes formulář. Vyplníš údaje, nahraješ soubory a odešleš — příjemci přijde email s přílohami.

## Jak spustit

**1. Nainstaluj PHP**

Stáhni PHP z [windows.php.net/download](https://windows.php.net/download/) a přidej ho do PATH, nebo použij Laragon/XAMPP.

**2. Nastav config**

Zkopíruj `src/config.example.php` jako `src/config.php` a vyplň SMTP údaje.

Já používám [Brevo](https://app.brevo.com) (free, 300 emailů/den) — po registraci najdeš SMTP údaje v sekci **SMTP & API**.

**3. Spusť server**

```
php -S localhost:8000 -t public -d upload_max_filesize=20M -d post_max_size=25M
```

**4. Otevři v prohlížeči**

```
http://localhost:8000
```
