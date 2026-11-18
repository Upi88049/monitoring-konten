<?php

namespace App\Enums;

enum EvidenceStatus: string
{
    case Ada = 'Ada';
    case Belum = 'Belum';

    public function isAvailable(): bool
    {
        return $this === self::Ada;
    }
}
