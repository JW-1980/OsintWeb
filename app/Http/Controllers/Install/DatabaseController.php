<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PDO;
use PDOException;

/**
 * Database configuration controller
 */
class DatabaseController extends Controller
{
    public function __construct(
        private readonly EnvironmentWriter $environmentWriter
    ) {
    }

    /**
     * Display database configuration page
     */
    public function index(): View
    {
        return view('install.database', [
            'host' => $this->environmentWriter->get('DB_HOST') ?? '127.0.0.1',
            'port' => $this->environmentWriter->get('DB_PORT') ?? '3306',
            'database' => $this->environmentWriter->get('DB_DATABASE') ?? 'osintweb',
            'username' => $this->environmentWriter->get('DB_USERNAME') ?? '',
        ]);
    }

    /**
     * Test database connection
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]+$/'],
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        try {
            $pdo = new PDO(
                "mysql:host={$validated['host']};port={$validated['port']}",
                $validated['username'],
                $validated['password'] ?? ''
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check MySQL version
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();

            if (!is_string($version)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine MySQL version',
                ]);
            }

            if (version_compare($version, '8.0', '<')) {
                return response()->json([
                    'success' => false,
                    'message' => "MySQL 8.0+ required. Found: {$version}",
                ]);
            }

            // Check if database exists
            $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
            $dbExists = in_array($validated['database'], $databases, true);

            // Check spatial extension support
            $spatialSupport = false;
            if ($dbExists) {
                $pdo->exec("USE `{$validated['database']}`");
                $result = $pdo->query("SHOW VARIABLES LIKE 'version'")->fetch(PDO::FETCH_ASSOC);
                $spatialSupport = $result !== false;
            }

            return response()->json([
                'success' => true,
                'database_exists' => $dbExists,
                'mysql_version' => $version,
                'spatial_support' => $spatialSupport,
            ]);
        } catch (PDOException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Save database configuration
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]+$/'],
            'username' => 'required|string',
            'password' => 'nullable|string',
            'create_database' => 'boolean',
        ]);

        // Create database if requested
        if ($request->boolean('create_database')) {
            try {
                $pdo = new PDO(
                    "mysql:host={$validated['host']};port={$validated['port']}",
                    $validated['username'],
                    $validated['password'] ?? ''
                );

                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$validated['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                return redirect()
                    ->back()
                    ->withErrors(['database' => 'Failed to create database: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        // Update .env file
        $this->environmentWriter->update([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $validated['host'],
            'DB_PORT' => (string) $validated['port'],
            'DB_DATABASE' => $validated['database'],
            'DB_USERNAME' => $validated['username'],
            'DB_PASSWORD' => $validated['password'] ?? '',
        ]);

        return redirect()->route('install.migrations');
    }
}
