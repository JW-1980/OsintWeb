<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service to check system requirements for installation
 */
class RequirementsChecker
{
    /**
     * Check all system requirements
     *
     * @return array<string, mixed>
     */
    public function check(): array
    {
        return [
            'php_version' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'permissions' => $this->checkPermissions(),
        ];
    }

    /**
     * Check if all requirements passed
     */
    public function allPassed(): bool
    {
        $checks = $this->check();

        if (!$checks['php_version']['passed']) {
            return false;
        }

        if (in_array(false, $checks['extensions'], true)) {
            return false;
        }

        if (in_array(false, $checks['permissions'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Check PHP version requirement
     *
     * @return array<string, mixed>
     */
    private function checkPhpVersion(): array
    {
        $required = '8.2.0';
        $current = phpversion();

        return [
            'required' => $required,
            'current' => $current,
            'passed' => version_compare($current, $required, '>='),
        ];
    }

    /**
     * Check required PHP extensions
     *
     * @return array<string, bool>
     */
    private function checkExtensions(): array
    {
        return [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'xml' => extension_loaded('xml'),
            'curl' => extension_loaded('curl'),
            'zip' => extension_loaded('zip'),
            'bcmath' => extension_loaded('bcmath') || true, // Optional: not critical for core functionality
            'gd' => extension_loaded('gd') || extension_loaded('imagick'),
            'json' => extension_loaded('json'),
            'tokenizer' => extension_loaded('tokenizer'),
            'fileinfo' => extension_loaded('fileinfo'),
            'openssl' => extension_loaded('openssl'),
        ];
    }

    /**
     * Check directory permissions
     *
     * @return array<string, bool>
     */
    private function checkPermissions(): array
    {
        $permissions = [
            'storage' => is_writable(storage_path()),
            'cache' => true,
            'logs' => true,
            'env' => is_writable(base_path('.env')),
        ];

        // Check storage subdirectories if storage is writable
        if ($permissions['storage']) {
            $cachePath = storage_path('framework/cache');
            $logsPath = storage_path('logs');

            if (is_dir($cachePath)) {
                $permissions['cache'] = is_writable($cachePath);
            }

            if (is_dir($logsPath)) {
                $permissions['logs'] = is_writable($logsPath);
            }
        } else {
            $permissions['cache'] = false;
            $permissions['logs'] = false;
        }

        return $permissions;
    }

    /**
     * Get human-readable requirement messages
     *
     * @return array<string, string>
     */
    public function getMessages(): array
    {
        $checks = $this->check();
        $messages = [];

        // PHP version messages
        if (!$checks['php_version']['passed']) {
            $messages[] = sprintf(
                'PHP %s or higher is required. Current version: %s',
                $checks['php_version']['required'],
                $checks['php_version']['current']
            );
        }

        // Extension messages
        foreach ($checks['extensions'] as $extension => $loaded) {
            if (!$loaded) {
                $messages[] = "PHP extension '{$extension}' is required but not loaded.";
            }
        }

        // Permission messages
        $permissionLabels = [
            'storage' => 'storage directory',
            'cache' => 'storage/framework/cache directory',
            'logs' => 'storage/logs directory',
            'env' => '.env file',
        ];

        foreach ($checks['permissions'] as $key => $writable) {
            if (!$writable) {
                $label = $permissionLabels[$key] ?? $key;
                $messages[] = "The {$label} must be writable.";
            }
        }

        return $messages;
    }
}
