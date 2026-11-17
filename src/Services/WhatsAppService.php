<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Sends donor/patient alerts over WhatsApp via the Meta Cloud API.
 *
 * In "stub mode" (WHATSAPP_ENABLED=false, or no phone_number_id/access_token
 * configured) nothing is sent over the network: the message is written to
 * the PHP error log instead, so the rest of the app works with zero WhatsApp
 * setup. Flip WHATSAPP_ENABLED=true and fill in the Meta credentials in .env
 * to start sending for real — no code changes needed.
 *
 * Meta requires a pre-approved message template to contact a user outside a
 * 24h customer-initiated session window, so every alert goes out as a
 * template with a single body placeholder (see WHATSAPP_TEMPLATE_NAME).
 */
final class WhatsAppService
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $apiVersion,
        private readonly ?string $phoneNumberId,
        private readonly ?string $accessToken,
        private readonly string $templateName,
        private readonly string $templateLang,
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            enabled: (bool) config('app.whatsapp.enabled', false),
            apiVersion: (string) config('app.whatsapp.api_version', 'v21.0'),
            phoneNumberId: config('app.whatsapp.phone_number_id') ?: null,
            accessToken: config('app.whatsapp.access_token') ?: null,
            templateName: (string) config('app.whatsapp.template_name', 'lifelink_alert'),
            templateLang: (string) config('app.whatsapp.template_lang', 'en'),
        );
    }

    public function isLive(): bool
    {
        return $this->enabled && $this->phoneNumberId !== null && $this->accessToken !== null;
    }

    /**
     * Send a one-line alert to a phone number. Returns true on success
     * (stub-mode "sends" always report success so calling code behaves the
     * same way in dev and production).
     */
    public function sendAlert(?string $toPhone, string $message): bool
    {
        $to = self::normalizePhone($toPhone);
        if ($to === null) {
            return false;
        }

        if (!$this->isLive()) {
            error_log(sprintf('[WhatsApp:stub] to=%s message=%s', $to, $message));
            return true;
        }

        return $this->sendViaMetaApi($to, $message);
    }

    private function sendViaMetaApi(string $to, string $message): bool
    {
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $this->apiVersion,
            $this->phoneNumberId,
        );

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'       => $this->templateName,
                'language'   => ['code' => $this->templateLang],
                'components' => [[
                    'type'       => 'body',
                    'parameters' => [['type' => 'text', 'text' => mb_substr($message, 0, 1024)]],
                ]],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError !== '' || $status < 200 || $status >= 300) {
            // Never log the access token; the response body is safe (Meta echoes no secrets).
            error_log(sprintf(
                '[WhatsApp] send failed to=%s status=%d curl_error=%s response=%s',
                $to,
                $status,
                $curlError,
                is_string($response) ? mb_substr($response, 0, 500) : '',
            ));
            return false;
        }

        return true;
    }

    /** Meta's API wants digits only (country code included, no "+"). */
    private static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        $digits = ltrim((string) preg_replace('/[^\d+]/', '', $phone), '+');

        return strlen($digits) >= 8 ? $digits : null;
    }
}
