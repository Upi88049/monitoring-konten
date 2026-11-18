<?php

namespace App\Enums;

enum DataStatus: string
{
    case Completed = 'Sudah diisi';
    case PendingH30 = 'Menunggu H+30';

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public function isPending(): bool
    {
        return $this === self::PendingH30;
    }
}
