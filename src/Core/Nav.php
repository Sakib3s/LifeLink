<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Single source of truth for the authenticated sidebar navigation per role.
 * Views never hard-code nav links — they render whatever this returns.
 */
final class Nav
{
    /**
     * @return array<int, array{label:string, href:string, icon:string}>
     */
    public static function forRole(string $role): array
    {
        return match ($role) {
            'donor' => [
                ['label' => 'Dashboard',   'href' => '/dashboard',        'icon' => 'layout-dashboard'],
                ['label' => 'My requests', 'href' => '/donor/matches',    'icon' => 'inbox'],
                ['label' => 'Donations',   'href' => '/donor/donations',  'icon' => 'droplet'],
                ['label' => 'Eligibility', 'href' => '/donor/eligibility','icon' => 'clipboard-check'],
                ['label' => 'Profile',     'href' => '/donor/profile',    'icon' => 'user'],
            ],
            'patient' => [
                ['label' => 'Dashboard',    'href' => '/dashboard',           'icon' => 'layout-dashboard'],
                ['label' => 'New request',  'href' => '/patient/requests/new','icon' => 'plus'],
                ['label' => 'My requests',  'href' => '/patient/requests',    'icon' => 'list-details'],
                ['label' => 'Profile',      'href' => '/patient/profile',     'icon' => 'user'],
            ],
            'blood_bank' => [
                ['label' => 'Dashboard',       'href' => '/dashboard',              'icon' => 'layout-dashboard'],
                ['label' => 'Inventory',       'href' => '/bloodbank/inventory',    'icon' => 'stack-2'],
                ['label' => 'Record donation', 'href' => '/bloodbank/donations/new','icon' => 'droplet-plus'],
                ['label' => 'Requests',        'href' => '/bloodbank/requests',     'icon' => 'inbox'],
                ['label' => 'Profile',         'href' => '/bloodbank/profile',      'icon' => 'building-hospital'],
            ],
            'admin' => [
                ['label' => 'Dashboard',     'href' => '/admin',              'icon' => 'layout-dashboard'],
                ['label' => 'Users',         'href' => '/admin/users',        'icon' => 'users'],
                ['label' => 'Verifications', 'href' => '/admin/verifications','icon' => 'shield-check'],
                ['label' => 'Requests',      'href' => '/admin/requests',     'icon' => 'clipboard-list'],
                ['label' => 'Reports',       'href' => '/admin/reports',      'icon' => 'chart-bar'],
                ['label' => 'Settings',      'href' => '/admin/settings',     'icon' => 'settings'],
                ['label' => 'Audit log',     'href' => '/admin/audit',        'icon' => 'history'],
            ],
            default => [
                ['label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'layout-dashboard'],
            ],
        };
    }

    public static function dashboardPathFor(string $role): string
    {
        return $role === 'admin' ? '/admin' : '/dashboard';
    }
}
