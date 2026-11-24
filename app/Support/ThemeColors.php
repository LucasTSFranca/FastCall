<?php

namespace App\Support;

class ThemeColors
{
    public static function hasAccessibleContrast(string $foreground, string $background): bool
    {
        return self::contrastRatio($foreground, $background) >= 4.5;
    }

    public static function contrastText(string $background): string
    {
        return self::contrastRatio('#000000', $background) >= self::contrastRatio('#ffffff', $background)
            ? '#000000'
            : '#ffffff';
    }

    private static function contrastRatio(string $firstColor, string $secondColor): float
    {
        $luminance = static function (string $color): float {
            $channels = array_map(
                static fn (string $channel): float => hexdec($channel) / 255,
                str_split(ltrim($color, '#'), 2)
            );
            $linearChannels = array_map(
                static fn (float $channel): float => $channel <= 0.04045
                    ? $channel / 12.92
                    : (($channel + 0.055) / 1.055) ** 2.4,
                $channels
            );

            return 0.2126 * $linearChannels[0]
                + 0.7152 * $linearChannels[1]
                + 0.0722 * $linearChannels[2];
        };

        $firstLuminance = $luminance($firstColor);
        $secondLuminance = $luminance($secondColor);

        return (max($firstLuminance, $secondLuminance) + 0.05)
            / (min($firstLuminance, $secondLuminance) + 0.05);
    }
}
