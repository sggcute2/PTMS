<?php

namespace App\Domain;

use InvalidArgumentException;

class TaskStatusTransition
{
    public const PENDING = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';

    public const ALLOWED_TRANSITIONS = [
        self::PENDING => self::IN_PROGRESS,
        self::IN_PROGRESS => self::COMPLETED,
    ];

    /**
     * Memvalidasi apakah transisi dari status awal ke target status valid
     */
    public static function canTransition(string $currentStatus, string $targetStatus): bool
    {
        return isset(self::ALLOWED_TRANSITIONS[$currentStatus])
            && self::ALLOWED_TRANSITIONS[$currentStatus] === $targetStatus;
    }

    /**
     * Mendapatkan status berikutnya
     */
    public static function nextStatus(string $currentStatus): ?string
    {
        return self::ALLOWED_TRANSITIONS[$currentStatus] ?? null;
    }

    /**
     * Cek apakah status sudah final
     */
    public static function isFinal(string $status): bool
    {
        return !isset(self::ALLOWED_TRANSITIONS[$status]);
    }
}
