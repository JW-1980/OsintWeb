<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

/**
 * Service to update .env file values
 */
class EnvironmentWriter
{
    /**
     * Update multiple environment variables
     *
     * @param array<string, string|null> $data Key-value pairs to update
     * @throws Exception
     */
    public function update(array $data): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            throw new Exception('.env file not found');
        }

        if (!is_writable($envPath)) {
            throw new Exception('.env file is not writable');
        }

        $envContent = file_get_contents($envPath);

        if ($envContent === false) {
            throw new Exception('Unable to read .env file');
        }

        foreach ($data as $key => $value) {
            $envContent = $this->updateOrAddKey($envContent, $key, $value);
        }

        if (file_put_contents($envPath, $envContent) === false) {
            throw new Exception('Unable to write to .env file');
        }

        // Clear config cache to reload new values
        if (function_exists('config')) {
            config()->offsetUnset('*');
        }
    }

    /**
     * Update an existing key or add it if not present
     */
    private function updateOrAddKey(string $content, string $key, ?string $value): string
    {
        $formattedValue = $this->formatValue($value);
        $pattern = "/^{$key}=.*/m";

        // Check if key exists
        if (preg_match($pattern, $content)) {
            // Update existing key
            return preg_replace($pattern, "{$key}={$formattedValue}", $content);
        }

        // Add new key at the end
        $content = rtrim($content, "\n");
        return $content . "\n{$key}={$formattedValue}\n";
    }

    /**
     * Format value for .env file
     * Adds quotes if value contains spaces or special characters
     */
    private function formatValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Check if value needs quoting
        if (
            str_contains($value, ' ') ||
            str_contains($value, '#') ||
            str_contains($value, '"') ||
            str_contains($value, "'")
        ) {
            // Escape double quotes and wrap in quotes
            $escaped = str_replace('"', '\\"', $value);
            return "\"{$escaped}\"";
        }

        return $value;
    }

    /**
     * Get current value from .env file
     */
    public function get(string $key): ?string
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return null;
        }

        $content = file_get_contents($envPath);

        if ($content === false) {
            return null;
        }

        $pattern = "/^{$key}=(.*)$/m";

        if (preg_match($pattern, $content, $matches)) {
            $value = $matches[1];

            // Remove quotes if present
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Unescape quotes
            $value = str_replace('\\"', '"', $value);

            return $value;
        }

        return null;
    }

    /**
     * Check if a key exists in .env file
     */
    public function has(string $key): bool
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        if ($content === false) {
            return false;
        }

        $pattern = "/^{$key}=/m";

        return preg_match($pattern, $content) === 1;
    }
}
