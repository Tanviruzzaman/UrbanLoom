<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
                            {--keep=14 : How many daily backups to keep}';

    protected $description = 'Dump the MySQL database to storage/app/backups and prune old copies';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->error("Only MySQL is supported; the current connection is [{$connection}].");

            return self::FAILURE;
        }

        $config = config("database.connections.{$connection}");
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $file = $directory.DIRECTORY_SEPARATOR.sprintf(
            '%s-%s.sql',
            $config['database'],
            now()->format('Y-m-d_His'),
        );

        $command = [
            $this->mysqldumpPath(),
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            // Skip locking so a backup never blocks a customer placing an order.
            '--single-transaction',
            '--quick',
            '--default-character-set=utf8mb4',
            '--result-file='.$file,
            $config['database'],
        ];

        // Passed by environment rather than argv so it never shows in a process list.
        $process = new Process($command, env: [
            'MYSQL_PWD' => (string) $config['password'],
        ]);
        $process->setTimeout(600);

        $this->info('Backing up '.$config['database'].'…');

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            @unlink($file);
            $this->error('Backup failed: '.trim($process->getErrorOutput() ?: $e->getMessage()));
            $this->line('Is mysqldump installed and on your PATH? Set MYSQLDUMP_PATH in .env if it lives elsewhere.');

            return self::FAILURE;
        }

        $size = File::exists($file) ? File::size($file) : 0;

        if ($size === 0) {
            @unlink($file);
            $this->error('Backup produced an empty file — nothing was saved.');

            return self::FAILURE;
        }

        $this->info(sprintf('Saved %s (%s)', basename($file), $this->humanSize($size)));

        $this->prune($directory, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * Delete all but the newest N backups.
     */
    private function prune(string $directory, int $keep): void
    {
        $backups = collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $stale = $backups->slice(max($keep, 1));

        $stale->each(fn ($file) => File::delete($file->getPathname()));

        if ($stale->isNotEmpty()) {
            $this->line('Removed '.$stale->count().' old '.str('backup')->plural($stale->count()).'.');
        }

        $this->line('Keeping '.min($backups->count(), max($keep, 1)).' backups in storage/app/backups.');
    }

    private function mysqldumpPath(): string
    {
        return env('MYSQLDUMP_PATH', 'mysqldump');
    }

    private function humanSize(int $bytes): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
