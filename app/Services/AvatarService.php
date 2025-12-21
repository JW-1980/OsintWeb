<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarService
{
    /**
     * Avatar types supported by the service.
     */
    public const TYPE_GENERATED = 'generated';
    public const TYPE_INITIALS = 'initials';
    public const TYPE_GRAVATAR = 'gravatar';
    public const TYPE_UPLOADED = 'uploaded';

    /**
     * Color palettes for generated avatars.
     */
    protected array $colorPalettes = [
        'vibrant' => [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
            '#F8B500', '#00CED1', '#FF69B4', '#32CD32', '#FF7F50',
        ],
        'pastel' => [
            '#FFB3BA', '#BAFFC9', '#BAE1FF', '#FFFFBA', '#FFDFBa',
            '#E0BBE4', '#957DAD', '#D291BC', '#FEC8D8', '#FFDFD3',
        ],
        'earth' => [
            '#8B4513', '#A0522D', '#CD853F', '#DEB887', '#D2691E',
            '#B8860B', '#DAA520', '#808000', '#6B8E23', '#556B2F',
        ],
        'ocean' => [
            '#006994', '#40E0D0', '#00CED1', '#20B2AA', '#48D1CC',
            '#5F9EA0', '#4682B4', '#6495ED', '#00BFFF', '#1E90FF',
        ],
    ];

    /**
     * Generate or retrieve avatar URL for a user.
     */
    public function getAvatarUrl(User $user, int $size = 128): string
    {
        return match ($user->avatar_type) {
            self::TYPE_UPLOADED => $user->avatar_url ?: $this->generateSvgDataUrl($user, $size),
            self::TYPE_GRAVATAR => $this->getGravatarUrl($user->email, $size),
            self::TYPE_INITIALS => $this->generateInitialsDataUrl($user, $size),
            default => $this->generateSvgDataUrl($user, $size),
        };
    }

    /**
     * Generate Gravatar URL.
     */
    public function getGravatarUrl(string $email, int $size = 128, string $default = 'identicon'): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}";
    }

    /**
     * Generate an SVG avatar as a data URL.
     */
    public function generateSvgDataUrl(User $user, int $size = 128): string
    {
        $svg = $this->generateSvg($user, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Generate an initials-based avatar as a data URL.
     */
    public function generateInitialsDataUrl(User $user, int $size = 128): string
    {
        $svg = $this->generateInitialsSvg($user, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Generate a geometric SVG avatar based on user seed.
     */
    public function generateSvg(User $user, int $size = 128): string
    {
        $seed = $user->avatar_seed ?: $this->generateSeed($user);
        $color = $user->avatar_color ?: $this->generateColor($seed);
        $bgColor = $this->lightenColor($color, 0.85);

        // Use seed to generate consistent random values
        $random = $this->seededRandom($seed);

        $pattern = $this->generatePattern($random, $color, $size);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}">
    <rect width="{$size}" height="{$size}" fill="{$bgColor}"/>
    {$pattern}
</svg>
SVG;
    }

    /**
     * Generate an initials-based SVG avatar.
     */
    public function generateInitialsSvg(User $user, int $size = 128): string
    {
        $initials = $this->getInitials($user->name);
        $seed = $user->avatar_seed ?: $this->generateSeed($user);
        $color = $user->avatar_color ?: $this->generateColor($seed);
        $textColor = $this->getContrastColor($color);
        $fontSize = $size * 0.4;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}">
    <rect width="{$size}" height="{$size}" fill="{$color}"/>
    <text x="50%" y="50%" dy="0.35em" fill="{$textColor}"
          font-family="system-ui, -apple-system, sans-serif"
          font-size="{$fontSize}px"
          font-weight="600"
          text-anchor="middle">{$initials}</text>
</svg>
SVG;
    }

    /**
     * Get user initials from name.
     */
    public function getInitials(string $name, int $maxChars = 2): string
    {
        $name = trim($name);
        if (empty($name)) {
            return '?';
        }

        $words = preg_split('/\s+/', $name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word) && strlen($initials) < $maxChars) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            }
        }

        return $initials ?: mb_strtoupper(mb_substr($name, 0, 1));
    }

    /**
     * Generate a consistent seed for a user.
     */
    public function generateSeed(User $user): string
    {
        return hash('sha256', $user->uuid . $user->email . $user->id);
    }

    /**
     * Generate a color from a seed string.
     */
    public function generateColor(string $seed, string $palette = 'vibrant'): string
    {
        $colors = $this->colorPalettes[$palette] ?? $this->colorPalettes['vibrant'];
        $index = abs(crc32($seed)) % count($colors);
        return $colors[$index];
    }

    /**
     * Generate a random color based on seed.
     */
    public function generateRandomColor(string $seed): string
    {
        $hash = md5($seed);
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        // Ensure colors are not too light or too dark
        $r = max(60, min(200, $r));
        $g = max(60, min(200, $g));
        $b = max(60, min(200, $b));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Lighten a hex color by a factor.
     */
    public function lightenColor(string $color, float $factor): string
    {
        $color = ltrim($color, '#');
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        $r = (int) round($r + (255 - $r) * $factor);
        $g = (int) round($g + (255 - $g) * $factor);
        $b = (int) round($b + (255 - $b) * $factor);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Get contrasting text color (black or white) for a background.
     */
    public function getContrastColor(string $bgColor): string
    {
        $color = ltrim($bgColor, '#');
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#1a1a1a' : '#ffffff';
    }

    /**
     * Create a seeded random number generator.
     */
    protected function seededRandom(string $seed): \Generator
    {
        $hash = md5($seed);
        $index = 0;

        while (true) {
            if ($index >= 32) {
                $hash = md5($hash);
                $index = 0;
            }
            $value = hexdec(substr($hash, $index, 2)) / 255;
            $index += 2;
            yield $value;
        }
    }

    /**
     * Generate a geometric pattern for the avatar.
     */
    protected function generatePattern(\Generator $random, string $color, int $size): string
    {
        $patternType = (int) ($random->current() * 4);
        $random->next();

        return match ($patternType) {
            0 => $this->generateCirclePattern($random, $color, $size),
            1 => $this->generatePolygonPattern($random, $color, $size),
            2 => $this->generateGridPattern($random, $color, $size),
            default => $this->generateWavePattern($random, $color, $size),
        };
    }

    /**
     * Generate circle pattern.
     */
    protected function generateCirclePattern(\Generator $random, string $color, int $size): string
    {
        $svg = '';
        $numCircles = 3 + (int) ($random->current() * 4);
        $random->next();

        for ($i = 0; $i < $numCircles; $i++) {
            $cx = $random->current() * $size;
            $random->next();
            $cy = $random->current() * $size;
            $random->next();
            $r = 10 + $random->current() * ($size * 0.3);
            $random->next();
            $opacity = 0.3 + $random->current() * 0.5;
            $random->next();

            $svg .= "<circle cx=\"{$cx}\" cy=\"{$cy}\" r=\"{$r}\" fill=\"{$color}\" opacity=\"{$opacity}\"/>";
        }

        return $svg;
    }

    /**
     * Generate polygon pattern.
     */
    protected function generatePolygonPattern(\Generator $random, string $color, int $size): string
    {
        $svg = '';
        $numShapes = 4 + (int) ($random->current() * 3);
        $random->next();

        for ($i = 0; $i < $numShapes; $i++) {
            $sides = 3 + (int) ($random->current() * 4);
            $random->next();
            $cx = $random->current() * $size;
            $random->next();
            $cy = $random->current() * $size;
            $random->next();
            $r = 15 + $random->current() * ($size * 0.25);
            $random->next();
            $rotation = $random->current() * 360;
            $random->next();
            $opacity = 0.3 + $random->current() * 0.5;
            $random->next();

            $points = [];
            for ($j = 0; $j < $sides; $j++) {
                $angle = ($j / $sides) * 2 * M_PI - M_PI / 2;
                $x = $cx + $r * cos($angle);
                $y = $cy + $r * sin($angle);
                $points[] = round($x, 2) . ',' . round($y, 2);
            }
            $pointsStr = implode(' ', $points);

            $svg .= "<polygon points=\"{$pointsStr}\" fill=\"{$color}\" opacity=\"{$opacity}\" transform=\"rotate({$rotation} {$cx} {$cy})\"/>";
        }

        return $svg;
    }

    /**
     * Generate grid pattern.
     */
    protected function generateGridPattern(\Generator $random, string $color, int $size): string
    {
        $svg = '';
        $gridSize = 4;
        $cellSize = $size / $gridSize;
        $padding = $cellSize * 0.15;

        for ($row = 0; $row < $gridSize; $row++) {
            for ($col = 0; $col < $gridSize; $col++) {
                // Mirror horizontally for symmetry
                $mirrorCol = $col < $gridSize / 2 ? $col : $gridSize - 1 - $col;
                $shouldDraw = $random->current() > 0.5;

                // Reset random for mirrored position
                if ($col >= $gridSize / 2) {
                    $random->next();
                    continue;
                }
                $random->next();

                if ($shouldDraw) {
                    $x = $col * $cellSize + $padding;
                    $y = $row * $cellSize + $padding;
                    $w = $cellSize - $padding * 2;
                    $h = $cellSize - $padding * 2;
                    $opacity = 0.6 + $random->current() * 0.4;
                    $random->next();

                    $svg .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" rx=\"4\" fill=\"{$color}\" opacity=\"{$opacity}\"/>";

                    // Draw mirrored cell
                    $mirrorX = ($gridSize - 1 - $col) * $cellSize + $padding;
                    $svg .= "<rect x=\"{$mirrorX}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" rx=\"4\" fill=\"{$color}\" opacity=\"{$opacity}\"/>";
                }
            }
        }

        return $svg;
    }

    /**
     * Generate wave pattern.
     */
    protected function generateWavePattern(\Generator $random, string $color, int $size): string
    {
        $svg = '';
        $numWaves = 3 + (int) ($random->current() * 3);
        $random->next();

        for ($i = 0; $i < $numWaves; $i++) {
            $y = ($i + 1) * ($size / ($numWaves + 1));
            $amplitude = 10 + $random->current() * 20;
            $random->next();
            $frequency = 1 + $random->current() * 2;
            $random->next();
            $opacity = 0.3 + $random->current() * 0.4;
            $random->next();

            $pathData = "M 0 {$y}";
            for ($x = 0; $x <= $size; $x += 5) {
                $waveY = $y + $amplitude * sin($x * $frequency * M_PI / $size);
                $pathData .= " L {$x} " . round($waveY, 2);
            }
            $pathData .= " L {$size} {$size} L 0 {$size} Z";

            $svg .= "<path d=\"{$pathData}\" fill=\"{$color}\" opacity=\"{$opacity}\"/>";
        }

        return $svg;
    }

    /**
     * Set avatar type and regenerate seed for a user.
     */
    public function setAvatarType(User $user, string $type, ?string $customColor = null): void
    {
        $validTypes = [self::TYPE_GENERATED, self::TYPE_INITIALS, self::TYPE_GRAVATAR, self::TYPE_UPLOADED];

        if (!in_array($type, $validTypes, true)) {
            throw new \InvalidArgumentException("Invalid avatar type: {$type}");
        }

        $user->avatar_type = $type;

        if ($type !== self::TYPE_UPLOADED) {
            $user->avatar_seed = $this->generateSeed($user);
            $user->avatar_color = $customColor ?: $this->generateColor($user->avatar_seed);
            $user->avatar_url = null;
        }

        $user->save();
    }

    /**
     * Regenerate avatar with new random seed.
     */
    public function regenerateAvatar(User $user, ?string $customColor = null): void
    {
        $user->avatar_seed = hash('sha256', $user->uuid . Str::random(32) . microtime());
        $user->avatar_color = $customColor ?: $this->generateColor($user->avatar_seed);

        if ($user->avatar_type === self::TYPE_UPLOADED) {
            $user->avatar_type = self::TYPE_GENERATED;
            $user->avatar_url = null;
        }

        $user->save();
    }

    /**
     * Handle avatar upload.
     */
    public function uploadAvatar(User $user, $file, string $disk = 'public'): string
    {
        // Delete old avatar if exists
        if ($user->avatar_url && $user->avatar_type === self::TYPE_UPLOADED) {
            $oldPath = str_replace('/storage/', '', $user->avatar_url);
            if (Storage::disk($disk)->exists($oldPath)) {
                Storage::disk($disk)->delete($oldPath);
            }
        }

        // Store new avatar
        $path = $file->store('avatars/' . $user->uuid, $disk);
        $url = Storage::disk($disk)->url($path);

        $user->update([
            'avatar_type' => self::TYPE_UPLOADED,
            'avatar_url' => $url,
        ]);

        return $url;
    }

    /**
     * Delete uploaded avatar and revert to generated.
     */
    public function deleteAvatar(User $user, string $disk = 'public'): void
    {
        if ($user->avatar_url && $user->avatar_type === self::TYPE_UPLOADED) {
            $path = str_replace('/storage/', '', $user->avatar_url);
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }

        $this->setAvatarType($user, self::TYPE_GENERATED);
    }

    /**
     * Get all available color palettes.
     */
    public function getColorPalettes(): array
    {
        return $this->colorPalettes;
    }

    /**
     * Get colors from a specific palette.
     */
    public function getPaletteColors(string $palette): array
    {
        return $this->colorPalettes[$palette] ?? $this->colorPalettes['vibrant'];
    }
}
