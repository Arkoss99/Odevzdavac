<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class SubmissionHandler
{
    private array $config;
    private string $uploadDir;
    private array $errors = [];
    private array $stored = [];

    public function __construct(array $config, string $uploadDir)
    {
        $this->config = $config;
        $this->uploadDir = rtrim($uploadDir, '/');
    }

    public function handle(array $post, array $files): array
    {
        $this->errors = [];
        $this->stored = [];

        $senderEmail    = trim($post['sender_email'] ?? '');
        $recipientEmail = trim($post['recipient_email'] ?? '');
        $nazev = $this->cleanText($post['nazev'] ?? '');
        $autor = $this->cleanText($post['autor'] ?? '');
        $popis = $this->cleanText($post['popis'] ?? '', 5000);

        if ($senderEmail === '' || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $this->errors['sender_email'] = 'Zadej platný e-mail odesílatele.';
        }
        if ($nazev === '' || $this->length($nazev) > 200) {
            $this->errors['nazev'] = 'Zadej název práce (max 200 znaků).';
        }
        if ($autor === '' || $this->length($autor) > 120) {
            $this->errors['autor'] = 'Zadej jméno autora (max 120 znaků).';
        }
        if ($popis === '') {
            $this->errors['popis'] = 'Zadej popis práce.';
        }
        if ($recipientEmail === '' || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->errors['recipient_email'] = 'Zadej platný e-mail příjemce.';
        }

        $cover = $this->singleFile($files['titulni'] ?? null);
        if ($cover === null) {
            $this->errors['titulni'] = 'Nahraj titulní fotku.';
        } elseif (!$this->isAllowedImage($cover)) {
            $this->errors['titulni'] = 'Titulní fotka musí být obrázek (JPG, PNG, WEBP, GIF).';
        } elseif (!$this->withinSize($cover)) {
            $this->errors['titulni'] = 'Titulní fotka je příliš velká.';
        }

        $photos = $this->multiFiles($files['fotky'] ?? null);
        foreach ($photos as $p) {
            if (!$this->isAllowedImage($p)) {
                $this->errors['fotky'] = 'Galerie smí obsahovat jen obrázky.';
                break;
            }
            if (!$this->withinSize($p)) {
                $this->errors['fotky'] = 'Některá fotka v galerii je příliš velká.';
                break;
            }
        }

        $attachments = $this->multiFiles($files['soubory'] ?? null);
        foreach ($attachments as $a) {
            if (!$this->withinSize($a)) {
                $this->errors['soubory'] = 'Některý soubor je příliš velký.';
                break;
            }
        }

        $total = count($photos) + count($attachments) + 1;
        if ($total > (int) $this->config['limits']['max_files']) {
            $this->errors['soubory'] = 'Překročen maximální počet souborů.';
        }

        if (!empty($this->errors)) {
            return ['ok' => false, 'errors' => $this->errors];
        }

        $coverPath = $this->store($cover);
        $photoPaths = array_map(fn($f) => $this->store($f), $photos);
        $filePaths = array_map(fn($f) => $this->store($f), $attachments);

        $payload = [
            'sender_email'    => $senderEmail,
            'recipient_email' => $recipientEmail,
            'nazev'  => $nazev,
            'autor'  => $autor,
            'popis'  => $popis,
            'cover'  => $coverPath,
            'photos' => $photoPaths,
            'files'  => $filePaths,
            'time'   => date('j. n. Y H:i'),
        ];

        try {
            $this->sendNotifications($payload);
        } catch (Exception $e) {
            return ['ok' => false, 'errors' => ['email' => 'E-mail se nepodařilo odeslat. Zkontroluj nastavení SMTP v src/config.php.']];
        }

        return ['ok' => true, 'message' => 'Práce byla odeslána. Potvrzení dorazilo na oba e-maily.'];
    }

    private function sendNotifications(array $p): void
    {
        $smtp = $this->config['smtp'];
        $mail = $this->config['mail'];

        foreach ([$p['sender_email'], $p['recipient_email']] as $to) {
            $m = new PHPMailer(true);
            $m->isSMTP();
            $m->Host = $smtp['host'];
            $m->SMTPAuth = true;
            $m->Username = $smtp['user'];
            $m->Password = $smtp['pass'];
            $m->SMTPSecure = $smtp['secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $m->Port = (int) $smtp['port'];
            $m->CharSet = 'UTF-8';

            $m->setFrom($mail['from_address'], $mail['from_name']);
            $m->addReplyTo($p['sender_email'], $p['autor']);
            $m->addAddress($to);
            $m->Subject = 'Nová práce: ' . $p['nazev'];

            $m->addAttachment($p['cover']['path'], $p['cover']['name']);
            foreach ($p['photos'] as $ph) {
                $m->addAttachment($ph['path'], $ph['name']);
            }
            foreach ($p['files'] as $f) {
                $m->addAttachment($f['path'], $f['name']);
            }

            $m->isHTML(true);
            $m->Body = $this->buildHtml($p);
            $m->AltBody = $this->buildText($p);

            $m->send();
        }
    }

    private function buildHtml(array $p): string
    {
        $e = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $photoCount = count($p['photos']);
        $fileCount = count($p['files']);

        $fileList = '';
        foreach ($p['files'] as $f) {
            $fileList .= '<li style="margin:2px 0;">' . $e($f['name']) . '</li>';
        }
        if ($fileList === '') {
            $fileList = '<li style="color:#888;">žádné</li>';
        }

        return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#16182b;">'
            . '<div style="background:#16182b;color:#fff;padding:24px 28px;border-radius:12px 12px 0 0;">'
            . '<div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;opacity:.7;">Nová odevzdaná práce</div>'
            . '<div style="font-size:22px;font-weight:700;margin-top:6px;">' . $e($p['nazev']) . '</div>'
            . '</div>'
            . '<div style="border:1px solid #e4e6ec;border-top:none;border-radius:0 0 12px 12px;padding:24px 28px;">'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:8px 0;color:#6b7280;width:120px;">Autor</td><td style="padding:8px 0;font-weight:600;">' . $e($p['autor']) . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">E-mail</td><td style="padding:8px 0;">' . $e($p['sender_email']) . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;vertical-align:top;">Popis</td><td style="padding:8px 0;white-space:pre-line;">' . $e($p['popis']) . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Fotky v galerii</td><td style="padding:8px 0;">' . $photoCount . '</td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;vertical-align:top;">Přiložené soubory (' . $fileCount . ')</td><td style="padding:8px 0;"><ul style="margin:0;padding-left:18px;">' . $fileList . '</ul></td></tr>'
            . '<tr><td style="padding:8px 0;color:#6b7280;">Odesláno</td><td style="padding:8px 0;">' . $e($p['time']) . '</td></tr>'
            . '</table>'
            . '<p style="font-size:12px;color:#9ca3af;margin-top:18px;">Titulní fotka a všechny soubory jsou v příloze tohoto e-mailu.</p>'
            . '</div></div>';
    }

    private function buildText(array $p): string
    {
        return "Nová odevzdaná práce\n\n"
            . "Název: {$p['nazev']}\n"
            . "Autor: {$p['autor']}\n"
            . "E-mail: {$p['sender_email']}\n"
            . "Popis:\n{$p['popis']}\n\n"
            . 'Fotky v galerii: ' . count($p['photos']) . "\n"
            . 'Přiložené soubory: ' . count($p['files']) . "\n"
            . "Odesláno: {$p['time']}\n";
    }

    private function cleanText(string $value, int $max = 1000): string
    {
        $value = trim($value);
        return $this->cut($value, $max);
    }

    private function length(string $value): int
    {
        return (int) preg_match_all('/./us', $value);
    }

    private function cut(string $value, int $max): string
    {
        if ($max < 0) {
            $max = 0;
        }
        return (string) preg_replace('/^(.{0,' . $max . '}).*$/us', '$1', $value);
    }

    private function singleFile($file): ?array
    {
        if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        return $file;
    }

    private function multiFiles($files): array
    {
        $out = [];
        if (!is_array($files) || !isset($files['name'])) {
            return $out;
        }
        $count = is_array($files['name']) ? count($files['name']) : 0;
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $out[] = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
        return $out;
    }

    private function isAllowedImage(array $file): bool
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        return in_array($mime, $this->config['limits']['allowed_images'], true);
    }

    private function withinSize(array $file): bool
    {
        return $file['size'] <= (int) $this->config['limits']['max_file_mb'] * 1024 * 1024;
    }

    private function store(array $file): array
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safe = bin2hex(random_bytes(8)) . ($ext !== '' ? '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext) : '');
        $dest = $this->uploadDir . '/' . $safe;
        move_uploaded_file($file['tmp_name'], $dest);
        return ['path' => $dest, 'name' => basename($file['name'])];
    }
}
