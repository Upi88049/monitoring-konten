<?php

namespace App\Enums;

enum ContentPillar: string
{
    case InternalCulture = 'Internal/Culture';
    case EmployerBranding = 'Employer Branding';
    case EmployeeBranding = 'Employee Branding';
    case EventJobFair = 'Event/Job Fair';
    case InfoLowongan = 'Info Lowongan';

    /**
     * Normalisasi string input ke ContentPillar.
     */
    public static function tryFromLoose(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $normalized) === 0) {
                return $case;
            }
        }

        return self::tryFrom($normalized);
    }
}
