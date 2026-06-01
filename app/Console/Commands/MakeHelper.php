<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeHelper extends Command
{
    protected $signature   = 'make:helper {name}';
    protected $description = 'Create a new helper class in app/Helpers';

    public function handle(): int
    {
        $name = $this->argument('name');
        $path = app_path("Helpers/{$name}.php");

        if (File::exists($path)) {
            $this->error("Helper [{$name}] already exists!");
            return Command::FAILURE;
        }

        if (!File::isDirectory(app_path('Helpers'))) {
            File::makeDirectory(app_path('Helpers'), 0755, true);
        }

        File::put($path, $this->getStub($name));

        $this->info("Helper [{$name}] created successfully!");
        $this->line("  → app/Helpers/{$name}.php");

        return Command::SUCCESS;
    }

    private function getStub(string $name): string
    {
        // FIX: heredoc marker must be alone on its own line
        $stub = '<?php' . "\n\n";
        $stub .= 'namespace App\Helpers;' . "\n\n";
        $stub .= 'use Illuminate\Http\JsonResponse;' . "\n\n";
        $stub .= "class {$name}\n{\n";

        $stub .= '
    // ── Success Response ───────────────────────────────────────────
    public static function success(
        string $message,
        mixed  $data   = [],
        int    $status = 200
    ): JsonResponse {
        return new JsonResponse([
            \'type\'    => \'success\',
            \'message\' => $message,
            \'result\'  => $data,
        ], $status);
    }

    // ── Error Response ─────────────────────────────────────────────
    public static function error(
        string $message,
        mixed  $errors = [],
        int    $status = 500
    ): JsonResponse {
        return new JsonResponse([
            \'type\'    => \'error\',
            \'message\' => $message,
            \'result\'  => $errors,
        ], $status);
    }

    // ── Validation Error Response ──────────────────────────────────
    public static function validation(
        mixed  $errors,
        string $message = \'Validation failed\'
    ): JsonResponse {
        return new JsonResponse([
            \'type\'    => \'error\',
            \'message\' => $message,
            \'result\'  => $errors,
        ], 422);
    }

    // ── Unauthorized Response ──────────────────────────────────────
    public static function unauthorized(
        string $message = \'Unauthorized\'
    ): JsonResponse {
        return new JsonResponse([
            \'type\'    => \'error\',
            \'message\' => $message,
            \'result\'  => [],
        ], 401);
    }

    // ── Not Found Response ─────────────────────────────────────────
    public static function notFound(
        string $message = \'Record not found\'
    ): JsonResponse {
        return new JsonResponse([
            \'type\'    => \'error\',
            \'message\' => $message,
            \'result\'  => [],
        ], 404);
    }

    // ── Paginated Response ─────────────────────────────────────────
    public static function paginated(
        string $message,
        array  $data    = [],
        int    $page    = 1,
        int    $perPage = 10,
        int    $total   = 0,
        string $type    = \'success\',
        int    $status  = 200
    ): JsonResponse {
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

        return new JsonResponse([
            \'type\'    => $type,
            \'message\' => $message,
            \'result\'  => [
                \'data\'       => $data,
                \'pagination\' => [
                    \'current_page\' => $page,
                    \'next_page\'    => $page < $lastPage ? $page + 1 : null,
                    \'prev_page\'    => $page > 1         ? $page - 1 : null,
                    \'last_page\'    => $lastPage,
                    \'per_page\'     => $perPage,
                    \'total\'        => $total,
                    \'has_more\'     => $page < $lastPage,
                ],
            ],
        ], $status);
    }

    // ── Empty Paginated Response ───────────────────────────────────
    public static function emptyPaginated(
        string $message = \'No results found.\',
        int    $page    = 1,
        int    $perPage = 10,
        string $type    = \'error\',
        int    $status  = 200
    ): JsonResponse {
        return new JsonResponse([
            \'type\'    => $type,
            \'message\' => $message,
            \'result\'  => [
                \'data\'       => [],
                \'pagination\' => [
                    \'current_page\' => $page,
                    \'next_page\'    => null,
                    \'prev_page\'    => null,
                    \'last_page\'    => 1,
                    \'per_page\'     => $perPage,
                    \'total\'        => 0,
                    \'has_more\'     => false,
                ],
            ],
        ], $status);
    }
}';

        return $stub;
    }
}