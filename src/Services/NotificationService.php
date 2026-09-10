<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;

/**
 * Creates in-app notifications. Kept as a service so the many call sites
 * (verification, matching, donation recording) share one entry point and
 * message shape. Delivery is in-app only for now; an email/SMS channel would
 * slot in here.
 */
final class NotificationService
{
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
}
