<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--path= : Custom backup path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = $this->option('path') ?: "C:\\Users\\Sta Rosa\\Desktop\\TheOneDesk Daily Backups\\Database Backups";

        // Create backup directory if it doesn't exist
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $backupFile = $backupPath . "\\{$dbName}_backup_{$timestamp}.sql";

        // Build mysqldump command
        $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u {$dbUser}";
        if ($dbPassword) {
            $command .= " -p{$dbPassword}";
        }
        $command .= " -h {$dbHost} {$dbName} > \"{$backupFile}\"";

        // Execute backup
        $result = shell_exec($command);

        if (file_exists($backupFile) && filesize($backupFile) > 0) {
            $this->info("Database backup created successfully: {$backupFile}");

            // Cleanup old backups (keep last 7 days)
            $this->cleanupOldBackups($backupPath);

            return 0;
        } else {
            $this->error("Database backup failed!");
            return 1;
        }
    }

    private function cleanupOldBackups($backupPath)
    {
        $files = glob($backupPath . "/*.sql");
        $cutoff = now()->subDays(7)->timestamp;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $this->info("Cleaned up old backup: " . basename($file));
            }
        }
    }
}
