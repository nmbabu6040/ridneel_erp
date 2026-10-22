<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupService
{
    public const DIRECTORY = 'backups';

    public function create(): string
    {
        $connection = config('database.connections.mysql');

        if (empty($connection['database'])) {
            throw new RuntimeException('Database name is missing.');
        }

        $dumpBinary = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        if (!File::exists($dumpBinary)) {
            throw new RuntimeException(
                'mysqldump.exe not found: ' . $dumpBinary
            );
        }

        $directory = storage_path('app/' . self::DIRECTORY);
        File::ensureDirectoryExists($directory);

        $filename = 'backup-' . now('Asia/Dhaka')->format('Y-m-d_His') . '.sql';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $optionsFile = tempnam(sys_get_temp_dir(), 'dbbk_');

        if ($optionsFile === false) {
            throw new RuntimeException(
                'Could not create temporary database options file.'
            );
        }

        try {
            $escapeOption = static function ($value): string {
                $value = (string) $value;

                return '"' . str_replace(
                    ["\\", '"', "\r", "\n"],
                    ["\\\\", '\\"', "\\r", "\\n"],
                    $value
                ) . '"';
            };

            $options = implode("\n", [
                '[client]',
                'protocol=tcp',
                'host=' . $escapeOption(
                    $connection['host'] ?? '127.0.0.1'
                ),
                'port=' . (int) ($connection['port'] ?? 3306),
                'user=' . $escapeOption(
                    $connection['username'] ?? 'root'
                ),
                'password=' . $escapeOption(
                    $connection['password'] ?? ''
                ),
                '',
            ]);

            if (file_put_contents($optionsFile, $options) === false) {
                throw new RuntimeException(
                    'Could not write temporary database options file.'
                );
            }

            @chmod($optionsFile, 0600);

            $env = [
                'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
                'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
                'PATH' => 'C:\\Windows\\System32;C:\\Windows;'
                    . 'C:\\xampp\\mysql\\bin;'
                    . (getenv('PATH') ?: ''),
                'TEMP' => sys_get_temp_dir(),
                'TMP' => sys_get_temp_dir(),
            ];

            $process = new Process([
                $dumpBinary,
                '--defaults-extra-file=' . $optionsFile,
                '--protocol=tcp',
                '--host=' . ($connection['host'] ?? '127.0.0.1'),
                '--port=' . (string) ($connection['port'] ?? 3306),
                '--single-transaction',
                '--skip-lock-tables',
                '--no-tablespaces',
                $connection['database'],
            ], base_path(), $env);

            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException(
                    'Database backup failed. Exit code: '
                        . $process->getExitCode()
                        . '. Error: '
                        . trim(
                            $process->getErrorOutput()
                                ?: $process->getOutput()
                        )
                );
            }

            $output = $process->getOutput();

            if ($output === '') {
                throw new RuntimeException(
                    'Backup command succeeded but returned empty output.'
                );
            }

            if (file_put_contents($path, $output, LOCK_EX) === false) {
                throw new RuntimeException(
                    'Could not write the backup SQL file.'
                );
            }

            if (!File::exists($path) || File::size($path) === 0) {
                throw new RuntimeException(
                    'Backup file is missing or empty.'
                );
            }

            return $filename;
        } catch (Throwable $e) {
            if (File::exists($path)) {
                File::delete($path);
            }

            throw $e;
        } finally {
            if (File::exists($optionsFile)) {
                File::delete($optionsFile);
            }
        }
    }


    public function list(): array
    {
        $directory = storage_path('app/' . self::DIRECTORY);

        if (!File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(
                fn($file) => strtolower($file->getExtension()) === 'sql'
            )
            ->sortByDesc(
                fn($file) => $file->getMTime()
            )
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'date' => \Carbon\Carbon::createFromTimestamp(
                    $file->getMTime(),
                    'Asia/Dhaka'
                ),
            ])
            ->values()
            ->all();
    }


    public function path(string $filename): string
    {
        return storage_path(
            'app/' . self::DIRECTORY
                . DIRECTORY_SEPARATOR
                . basename($filename)
        );
    }

    public function delete(string $filename): bool
    {
        $safeFilename = basename($filename);

        if (
            $safeFilename !== $filename
            || strtolower(pathinfo(
                $safeFilename,
                PATHINFO_EXTENSION
            )) !== 'sql'
        ) {
            return false;
        }

        $path = $this->path($safeFilename);

        if (!File::exists($path) || !File::isFile($path)) {
            return false;
        }

        return File::delete($path);
    }

    public function prune(int $keep = 10): int
    {
        $keep = max(0, $keep);
        $backups = $this->list();
        $deleted = 0;

        foreach (array_slice($backups, $keep) as $backup) {
            if ($this->delete($backup['filename'])) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
