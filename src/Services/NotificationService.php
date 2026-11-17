<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;

/**
 * Creates in-app notifications and, where a phone number is available and
 * the caller opts in, mirrors the alert over WhatsApp (via WhatsAppService).
 * Kept as one entry point so every call site (verification, matching,
 * donation recording) shares the same message shape.
 */
final class NotificationService
{
    private ?WhatsAppService $whatsApp = null;

    public function __construct(?WhatsAppService $whatsApp = null)
    {
        $this->whatsApp = $whatsApp;
    }

    public function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $relatedType = null,
        ?int $relatedId = null,
    ): int {
        return Notification::insert([
            'user_id'      => $userId,
            'type'         => $type,
            'title'        => mb_substr($title, 0, 160),
            'message'      => mb_substr($message, 0, 500),
            'related_type' => $relatedType,
            'related_id'   => $relatedId,
            'is_read'      => 0,
        ]);
    }

    /**
     * Fan a notification out to several users (e.g. all matched donors).
     *
     * @param array<int, int> $userIds
     */
    public function notifyMany(array $userIds, string $type, string $title, string $message, ?string $relatedType = null, ?int $relatedId = null): void
    {
        foreach (array_unique($userIds) as $userId) {
            $this->notify((int) $userId, $type, $title, $message, $relatedType, $relatedId);
        }
    }

    /**
     * Same as notify(), but also pushes a WhatsApp alert to $phone when one
     * is given. Use this for time-sensitive alerts the recipient should see
     * even if they are not watching the bell (e.g. "you have a compatible
     * blood request nearby", verification outcomes). The in-app notification
     * is always created even if the WhatsApp send fails or is stubbed.
     */
    public function notifyWithWhatsApp(
        int $userId,
        ?string $phone,
        string $type,
        string $title,
        string $message,
        ?string $relatedType = null,
        ?int $relatedId = null,
    ): int {
        $id = $this->notify($userId, $type, $title, $message, $relatedType, $relatedId);

        if ($phone !== null && $phone !== '') {
            $this->whatsAppClient()->sendAlert($phone, $title . ': ' . $message);
        }

        return $id;
    }

    private function whatsAppClient(): WhatsAppService
    {
        return $this->whatsApp ??= WhatsAppService::fromConfig();
    }
}
