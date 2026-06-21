# Odevzdávání prací — webová aplikace

Webová aplikace v **PHP + JavaScriptu**, která přijímá data o práci, ukládá nahrané soubory a po odeslání rozešle **dvě e‑mailové notifikace** přes SMTP — jednu na odesílací účet (kopie) a druhou na e‑mail příjemce.

Formulář sbírá: **Název práce, Autor, Popis práce, Titulní fotka** (povinné), **Galerie fotek** a **Soubory** (nepovinné).

---

## Co budeš potřebovat

- **PHP 8.1 nebo novější** (ověřit: `php -v`)
- Funkční **SMTP účet** pro odesílání e‑mailů (např. Gmail, Seznam, školní e‑mail)

PHPMailer je už přibalený ve složce `lib/`, takže nemusíš nic doinstalovávat. Composer je volitelný (viz níže).

### Když ještě nemáš PHP

- **Windows:** stáhni a rozbal PHP z [windows.php.net/download](https://windows.php.net/download/) a přidej složku do `PATH`, nebo nainstaluj [Laragon](https://laragon.org/) / [XAMPP](https://www.apachefriends.org/).
- **macOS:** `brew install php`
- **Linux:** `sudo apt install php-cli`

---

## Spuštění (3 kroky)

### 1) Nastav SMTP a e‑maily

Otevři soubor **`src/config.php`** a vyplň své údaje:

```php
'smtp' => [
    'host'   => 'smtp.gmail.com',          // SMTP server
    'port'   => 587,                       // 587 = TLS, 465 = SSL
    'secure' => 'tls',                     // 'tls' nebo 'ssl'
    'user'   => 'tvuj-ucet@gmail.com',     // přihlašovací jméno
    'pass'   => 'app-password',            // heslo (u Gmailu App Password)
],

'mail' => [
    'from_address' => 'tvuj-ucet@gmail.com',   // odesílatel
    'from_name'    => 'Odevzdávání prací',
    'sender_copy'  => 'tvuj-ucet@gmail.com',   // sem dorazí kopie (odesílací účet)
    'recipient'    => 'ucitel@skola.cz',       // sem dorazí notifikace o nové práci
],
```

> **Gmail:** běžné heslo nebude fungovat. Zapni dvoufázové ověření a vygeneruj **App Password** na [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords). To 16místné heslo vlož do `pass`.
>
> **Seznam.cz:** `host` = `smtp.seznam.cz`, `port` = `465`, `secure` = `ssl`.

### 2) Spusť server

V kořenové složce projektu zadej:

```bash
php -S localhost:8000 -t public -d upload_max_filesize=20M -d post_max_size=25M
```

Parametry `-d` zvedají limit velikosti nahrávaných souborů (uprav podle potřeby).

### 3) Otevři aplikaci

V prohlížeči zadej:

```
http://localhost:8000
```

Vyplň formulář, nahraj titulní fotku, klikni na **Odeslat práci** — během chvíle dorazí oba e‑maily s daty a přílohami.

---

## Jak to funguje

```
projekt/
├── public/              ← veřejná část (sem míří server)
│   ├── index.php        ← formulář (HTML + napojení na styly/skript)
│   ├── submit.php       ← příjem dat, vrací JSON
│   └── assets/
│       ├── style.css    ← vzhled
│       └── app.js       ← náhledy, drag & drop, odeslání přes fetch()
├── src/
│   ├── config.php           ← NASTAVENÍ (SMTP, e‑maily, limity)
│   ├── bootstrap.php        ← načtení PHPMaileru
│   └── SubmissionHandler.php← validace, uložení souborů, odeslání e‑mailů
├── lib/PHPMailer/       ← knihovna pro odesílání e‑mailů (přibalená)
├── uploads/             ← sem se ukládají nahrané soubory (mimo veřejnou složku)
└── composer.json        ← volitelná instalace přes Composer
```

1. `app.js` odešle formulář metodou `fetch()` jako `FormData` na `submit.php` (stránka se nereloaduje).
2. `submit.php` předá data třídě `SubmissionHandler`.
3. Handler **zvaliduje** vstupy, **uloží soubory** pod náhodnými názvy do `uploads/` a přes **PHPMailer** odešle dva e‑maily.
4. Zpět přijde JSON `{ "ok": true }`, nebo `{ "ok": false, "errors": {...} }` s chybami u jednotlivých polí.

---

## Na čem stojí zabezpečení

- Soubory se ukládají **mimo veřejnou složku** (`uploads/` není přístupné z prohlížeče) a dostávají **náhodný název** — předchází se přepsání i spuštění nahraného kódu.
- Typ obrázku se ověřuje podle **skutečného obsahu** (`finfo`), ne podle koncovky.
- Hlídá se **velikost** i **počet** souborů (nastavitelné v `config.php`).
- Veškerý text v e‑mailu je **ošetřen** proti vložení HTML (`htmlspecialchars`).
- Vstupy se validují **na serveru** (klientská validace je jen pro pohodlí).

---

## Volitelně: instalace přes Composer

Pokud preferuješ Composer, knihovnu si můžeš stáhnout sám:

```bash
composer install
```

Aplikace automaticky použije `vendor/`, pokud existuje; jinak sáhne po přibalené verzi v `lib/`.

---

## Časté potíže

| Problém | Řešení |
|---|---|
| „E‑mail se nepodařilo odeslat“ | Zkontroluj údaje v `src/config.php`. U Gmailu musíš použít App Password. |
| Velký soubor se nenahraje | Zvyš `-d upload_max_filesize` a `-d post_max_size` při spouštění serveru. |
| `php: command not found` | PHP není nainstalované nebo není v `PATH` (viz sekce nahoře). |
| Fonty se nenačtou | Stránka používá Google Fonts — je potřeba připojení k internetu. |
