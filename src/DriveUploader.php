<?php

class DriveUploader
{
    private string $accessToken;
    private string $folderId;

    public function __construct(string $credentialsJson, string $folderId)
    {
        $this->folderId = $folderId;
        $creds = json_decode($credentialsJson, true);
        $this->accessToken = $this->getAccessToken($creds);
    }

    public function createFolder(string $name): string
    {
        $metadata = json_encode([
            'name'     => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents'  => [$this->folderId],
        ]);

        $ch = curl_init('https://www.googleapis.com/drive/v3/files?supportsAllDrives=true&fields=id');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $metadata,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->accessToken}",
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (empty($response['id'])) {
            throw new RuntimeException('Drive folder creation failed: ' . json_encode($response));
        }

        return $response['id'];
    }

    public function upload(string $filePath, string $fileName, string $mimeType, string $parentId = ''): string
    {
        $parent   = $parentId ?: $this->folderId;
        $metadata = json_encode(['name' => $fileName, 'parents' => [$parent]]);
        $fileContent = file_get_contents($filePath);
        $boundary    = 'boundary_' . bin2hex(random_bytes(8));

        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . $metadata . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$mimeType}\r\n\r\n"
            . $fileContent . "\r\n"
            . "--{$boundary}--";

        $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,webViewLink&supportsAllDrives=true');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->accessToken}",
                "Content-Type: multipart/related; boundary={$boundary}",
                'Content-Length: ' . strlen($body),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (empty($response['id'])) {
            throw new RuntimeException('Drive upload failed: ' . json_encode($response));
        }

        $this->makePublic($response['id']);
        return $response['webViewLink'];
    }

    private function makePublic(string $fileId): void
    {
        $ch = curl_init("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions?supportsAllDrives=true");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['type' => 'anyone', 'role' => 'reader']),
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->accessToken}",
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function getAccessToken(array $creds): string
    {
        $now     = time();
        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'iss'   => $creds['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = "{$header}.{$payload}";
        $key          = openssl_pkey_get_private($creds['private_key']);
        openssl_sign($signingInput, $sig, $key, OPENSSL_ALGO_SHA256);
        $jwt = "{$signingInput}." . $this->base64url($sig);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (empty($resp['access_token'])) {
            throw new RuntimeException('Google auth failed: ' . json_encode($resp));
        }
        return $resp['access_token'];
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
