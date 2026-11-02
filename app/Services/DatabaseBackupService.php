<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Runs `mysqldump` via Symfony\Process (already bundled with every
 * Laravel install — no composer package had to be added for this,
 * which matters since this environment couldn't reach the internet to
 * install one). Requires the `mysqldump` binary to exist on the
 * server's PATH, which is true on effectively every shared/VPS hosting
 * plan that offers MySQL — see README for the one thing to check if
 * it isn't.
 */
class DatabaseBackupService
{
    public const DIRECTORY = 'backups';

    public function create(): string
    {
        $disk = storage_path('app/' . self::DIRECTORY);
        File::ensureDirectoryExists($disk);

        $filename = 'backup-' . now()->format('Y-m-d_His') . '.sql';
        $path = $disk . DIRECTORY_SEPARATOR . $filename;

        // Credentials go in a short-lived options file instead of a
        // --password=xxx CLI argument — a password on the command line
        // is visible to any other user on the server running `ps aux`
        // while mysqldump executes. This file is deleted immediately
        // after, success or failure, via the try/finally below.
        $optionsFile = tempnam(sys_get_temp_dir(), 'dbbk');
        file_put_contents($optionsFile, sprintf(
            "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
            config('database.connections.mysql.host'),
            config('database.connections.mysql.port'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password')
        ));
        chmod($optionsFile, 0600);

        try {
            $process = new Process([
                'mysqldump',
                '--defaults-extra-file=' . $optionsFile,
                '--single-transaction',
                '--skip-lock-tables',
                config('database.connections.mysql.database'),
            ]);

            $process->setTimeout(300);
            $process->run(function ($type, $buffer) use ($path) {
                if ($type === Process::OUT) {
                    File::append($path, $buffer);
                }
            });

            if (!$process->isSuccessful() || !File::exists($path) || File::size($path) === 0) {
                File::delete($path); // don't leave a 0-byte / partial file behind
                throw new \RuntimeException(
                    'mysqldump failed: ' . $process->getErrorOutput() .
                    ' — is the `mysqldump` binary installed and on PATH on this server?'
                );
            }
        } finally {
            @unlink($optionsFile);
        }

        return $filename;
    }

    /** @return array<int, array{name: string, size: int, date: \Illuminate\Support\Carbon}> */
    public function list(): array
    {
        $disk = storage_path('app/' . self::DIRECTORY);
        if (!File::isDirectory($disk)) {
            return [];
        }

        return collect(File::files($disk))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->map(fn ($f) => [
                'name' => $f->getFilename(),
                'size' => $f->getSize(),
                'date' => \Illuminate\Support\Carbon::createFromTimestamp($f->getMTime()),
            ])
            ->values()
            ->all();
    }

    public function path(string $filename): string
    {
        return storage_path('app/' . self::DIRECTORY . '/' . basename($filename));
    }

    public function delete(string $filename): void
    {
        File::delete($this->path($filename));
    }

    /** Keep only the most recent $keep backups — called by the scheduled command. */
    public function prune(int $keep = 14): int
    {
        $files = $this->list();
        $toDelete = array_slice($files, $keep);

        foreach ($toDelete as $file) {
            $this->delete($file['name']);
        }

        return count($toDelete);
    }
}
