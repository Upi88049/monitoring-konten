<?php

namespace App\Enums;

enum ContentType: string
{
    case Feed = 'Feed';
    case Reels = 'Reels';
    case Carousel = 'Carousel';

    /**
     * Normalisasi string input (misal typo 'Feeds' dari file Excel) ke ContentType.
     */
    public static function tryFromLoose(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        if (strcasecmp($normalized, 'Feeds') === 0 || strcasecmp($normalized, 'Feed') === 0) {
            return self::Feed;
        }

        if (strcasecmp($normalized, 'Reel') === 0 || strcasecmp($normalized, 'Reels') === 0) {
            return self::Reels;
        }

        if (strcasecmp($normalized, 'Carousel') === 0) {
            return self::Carousel;
        }

        return self::tryFrom($normalized);
    }
}
