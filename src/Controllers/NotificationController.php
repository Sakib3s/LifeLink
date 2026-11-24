<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Paginator;
use App\Models\Notification;

/**
 * In-app notifications: full page, plus JSON endpoints backing the navbar bell.
 */
final class NotificationController extends Controller
{
    private const PER_PAGE = 15;

    public function index(): never
    {
        $user = $this->requireAuth();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = Notification::paginateFor((int) $user['id'], $page, self::PER_PAGE);
        $paginator = new Paginator($result['total'], self::PER_PAGE, $page, '/notifications');

        $this->view('notifications/index', [
            'title'         => 'Notifications — LifeLink',
            'user'          => $user,
            'pageTitle'     => 'Notifications',
            'notifications' => $result['rows'],
            'paginator'     => $paginator,
        ]);
    }

    /** JSON feed for the bell dropdown. */
    public function recent(): never
    {
        $user = $this->requireAuth();
        $uid = (int) $user['id'];

        $this->json([
            'unread' => Notification::unreadCount($uid),
            'items'  => array_map(static fn (array $n): array => [
                'id'      => (int) $n['id'],
                'title'   => (string) $n['title'],
                'message' => (string) $n['message'],
                'is_read' => (bool) $n['is_read'],
                'time'    => date('M j, g:i a', strtotime((string) $n['created_at'])),
            ], Notification::recentFor($uid, 10)),
        ]);
    }

    public function markRead(string $id): never
    {
        $user = $this->requireAuth();
        Notification::markRead((int) $id, (int) $user['id']);

        $this->respond((int) $user['id']);
    }

    public function markAllRead(): never
    {
        $user = $this->requireAuth();
        Notification::markAllRead((int) $user['id']);

        $this->respond((int) $user['id']);
    }

    private function respond(int $userId): never
    {
        if ($this->request->wantsJson() || $this->request->isAjax()) {
            $this->json(['unread' => Notification::unreadCount($userId)]);
        }

        $this->back('/notifications');
    }
}
