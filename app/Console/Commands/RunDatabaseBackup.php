<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class RunDatabaseBackup extends Command
{
    protected $signature = 'app:backup-database {--keep=14 : How many recent backups to retain}';
    protected $description = 'Create a database backup and prune old ones (run daily via scheduler)';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $filename = $backups->create();
            $this->info("Backup created: {$filename}");
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $pruned = $backups->prune((int) $this->option('keep'));
        if ($pruned > 0) {
            $this->info("Pruned {$pruned} old backup(s).");
        }

        return self::SUCCESS;
    }
}
