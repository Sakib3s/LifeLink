<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Donation extends Model
{
    protected static string $table = 'donations';

    /**
     * @param array<string, mixed> $data
     */
    public static function record(array $data): int
    {
        $id = self::insert([
            'donor_id'         => $data['donor_id'],
            'blood_bank_id'    => $data['blood_bank_id'] ?? null,
            'blood_request_id' => $data['blood_request_id'] ?? null,
            'blood_group'      => $data['blood_group'],
            'units'            => $data['units'] ?? 1,
            'donation_date'    => $data['donation_date'],
            'notes'            => $data['notes'] ?? null,
        ]);

        Database::run(
            'UPDATE donors SET total_donations = total_donations + 1, last_donation_date = :d
              WHERE id = :id AND (last_donation_date IS NULL OR last_donation_date < :d)',
            ['id' => $data['donor_id'], 'd' => $data['donation_date']],
        );

        return $id;
    }

    /** @return array<int, array<string, mixed>> */
    public static function forDonor(int $donorId): array
    {
        return Database::all(
            'SELECT dn.*, bb.bank_name
               FROM donations dn LEFT JOIN blood_banks bb ON bb.id = dn.blood_bank_id
              WHERE dn.donor_id = :donor_id
              ORDER BY dn.donation_date DESC, dn.id DESC',
            ['donor_id' => $donorId],
        );
    }
}
