<?php

namespace App\Console\Commands;


use App\Services\BackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Membuat backup database dan storage RudyPOS';

    public function handle(BackupService $backupService): int
    {
        $this->info('Memulai backup RudyPOS...');

        try {

            $path = $backupService->create();

            $this->newLine();

            $this->info('Backup berhasil dibuat!');

            $this->line('File: ' . $path);

            return self::SUCCESS;

        } catch (Throwable $e) {

            $this->newLine();

            $this->error('Backup gagal!');

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}