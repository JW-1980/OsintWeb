<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Database migration controller
 */
class MigrationController extends Controller
{
    /**
     * Display migrations page
     */
    public function index(): View
    {
        return view('install.migrations');
    }

    /**
     * Run database migrations
     */
    public function run(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seed_data' => 'boolean',
            'sample_conflicts' => 'boolean',
        ]);

        $output = new BufferedOutput();

        try {
            // Run migrations
            Artisan::call('migrate', [
                '--force' => true,
            ], $output);

            $migrationOutput = $output->fetch();

            // Optionally seed initial data
            if ($request->boolean('seed_data')) {
                Artisan::call('db:seed', [
                    '--class' => 'InitialDataSeeder',
                    '--force' => true,
                ], $output);
            }

            // Optionally seed sample conflicts
            if ($request->boolean('sample_conflicts')) {
                Artisan::call('db:seed', [
                    '--class' => 'ConflictsSeeder',
                    '--force' => true,
                ], $output);
            }

            return response()->json([
                'success' => true,
                'output' => $output->fetch(),
                'migration_output' => $migrationOutput,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'output' => $output->fetch(),
            ], 500);
        }
    }
}
