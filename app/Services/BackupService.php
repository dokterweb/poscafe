<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupService
{
    /**
     * Membuat backup database + storage menjadi ZIP.
     */
    public function create(): string
    {
        $disk = Storage::disk(config('backup.disk', 'local'));

        $backupDirectory =config('backup.directory', 'backups');


        if (!$backupDirectory) {

            throw new RuntimeException(
                'Konfigurasi direktori backup tidak tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan folder backup tersedia
        |--------------------------------------------------------------------------
        */

        $disk->makeDirectory($backupDirectory);


        /*
        |--------------------------------------------------------------------------
        | Nama Backup
        |--------------------------------------------------------------------------
        */

        $timestamp = now()->format('Y-m-d_H-i-s');

        $backupName = 'rudypos_backup_' . $timestamp;

        $temporaryDirectory =
            $backupDirectory . '/' . $backupName;


        /*
        |--------------------------------------------------------------------------
        | Buat Folder Temporary
        |--------------------------------------------------------------------------
        */

        $disk->makeDirectory($temporaryDirectory);


        /*
        |--------------------------------------------------------------------------
        | File Database
        |--------------------------------------------------------------------------
        */

        $databaseFile =
            $disk->path(
                $temporaryDirectory . '/database.sql'
            );


        /*
        |--------------------------------------------------------------------------
        | Backup Database
        |--------------------------------------------------------------------------
        */

        $this->backupDatabase($databaseFile);


        /*
        |--------------------------------------------------------------------------
        | Backup Storage
        |--------------------------------------------------------------------------
        */

        $this->backupStorage(
            $disk,
            $temporaryDirectory
        );


        /*
        |--------------------------------------------------------------------------
        | Buat ZIP
        |--------------------------------------------------------------------------
        */

        $zipRelativePath =
            $backupDirectory .
            '/' .
            $backupName .
            '.zip';

        $zipPath =
            $disk->path($zipRelativePath);


        $this->createZip(
            $disk->path($temporaryDirectory),
            $zipPath
        );


        /*
        |--------------------------------------------------------------------------
        | Hapus Temporary Folder
        |--------------------------------------------------------------------------
        */

        $disk->deleteDirectory(
            $temporaryDirectory
        );


        /*
        |--------------------------------------------------------------------------
        | Return Relative Path
        |--------------------------------------------------------------------------
        */

        return $zipRelativePath;
    }


   protected function backupDatabase(string $outputFile): void
    {
        $mysqlDump = config(
            'backup.mysqldump_path',
            'mysqldump'
        );

        $host = config(
            'database.connections.mysql.host',
            '127.0.0.1'
        );

        $port = config(
            'database.connections.mysql.port',
            3306
        );

        $database = config(
            'database.connections.mysql.database'
        );

        $username = config(
            'database.connections.mysql.username'
        );

        $password = config(
            'database.connections.mysql.password'
        );


        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        if (!$database) {
            throw new RuntimeException(
                'Nama database belum dikonfigurasi.'
            );
        }

        if (!$username) {
            throw new RuntimeException(
                'Username database belum dikonfigurasi.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Command
        |--------------------------------------------------------------------------
        */

        $command = [
            $mysqlDump,

            '--protocol=TCP',

            '--host=' . $host,

            '--port=' . $port,

            '--user=' . $username,

            '--single-transaction',

            '--routines',

            '--triggers',

            '--result-file=' . $outputFile,

            $database,
        ];


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        if (
            $password !== null &&
            trim((string) $password) !== ''
        ) {

            $command[] =
                '--password=' . $password;
        }


        /*
        |--------------------------------------------------------------------------
        | Log Informasi
        |--------------------------------------------------------------------------
        */

        Log::info(
            'RudyPOS Backup Database',
            [
                'mysqldump' => $mysqlDump,
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'has_password' =>
                    $password !== null &&
                    trim((string) $password) !== '',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Jalankan mysqldump
        |--------------------------------------------------------------------------
        */

        $process = new Process($command);

        $process->setTimeout(300);

        $process->run();


        /*
        |--------------------------------------------------------------------------
        | Cek Error
        |--------------------------------------------------------------------------
        */

        if (!$process->isSuccessful()) {

            throw new RuntimeException(
                'Backup database gagal: ' .
                trim($process->getErrorOutput())
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pastikan File Ada
        |--------------------------------------------------------------------------
        */

        if (!file_exists($outputFile)) {

            throw new RuntimeException(
                'File database.sql tidak berhasil dibuat.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pastikan File Tidak Kosong
        |--------------------------------------------------------------------------
        */

        if (filesize($outputFile) === 0) {

            throw new RuntimeException(
                'File database.sql berhasil dibuat tetapi kosong.'
            );
        }
    }


    /**
     * Backup storage/app/public.
     */
    protected function backupStorage(
        $disk,
        string $temporaryDirectory
    ): void {

        $storagePath =
            storage_path('app/public');


        /*
        |--------------------------------------------------------------------------
        | Jika storage belum ada
        |--------------------------------------------------------------------------
        */

        if (!is_dir($storagePath)) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Folder tujuan
        |--------------------------------------------------------------------------
        */

        $destination =
            $disk->path(
                $temporaryDirectory . '/storage/public'
            );


        /*
        |--------------------------------------------------------------------------
        | Buat Folder
        |--------------------------------------------------------------------------
        */

        if (!is_dir($destination)) {

            mkdir(
                $destination,
                0755,
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Copy Directory
        |--------------------------------------------------------------------------
        */

        $this->copyDirectory(
            $storagePath,
            $destination
        );
    }


    /**
     * Copy directory secara recursive.
     */
    protected function copyDirectory(
        string $source,
        string $destination
    ): void {

        $items =
            scandir($source);


        if ($items === false) {

            return;
        }


        foreach ($items as $item) {

            if (
                $item === '.' ||
                $item === '..'
            ) {
                continue;
            }


            $sourcePath =
                $source . DIRECTORY_SEPARATOR . $item;

            $destinationPath =
                $destination . DIRECTORY_SEPARATOR . $item;


            /*
            |--------------------------------------------------------------------------
            | Directory
            |--------------------------------------------------------------------------
            */

            if (is_dir($sourcePath)) {

                if (!is_dir($destinationPath)) {

                    mkdir(
                        $destinationPath,
                        0755,
                        true
                    );
                }


                $this->copyDirectory(
                    $sourcePath,
                    $destinationPath
                );


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | File
            |--------------------------------------------------------------------------
            */

            copy(
                $sourcePath,
                $destinationPath
            );
        }
    }


    /**
     * Membuat file ZIP.
     */
    protected function createZip(
        string $sourceDirectory,
        string $zipPath
    ): void {

        $zip = new ZipArchive();


        /*
        |--------------------------------------------------------------------------
        | Open ZIP
        |--------------------------------------------------------------------------
        */

        $result =
            $zip->open(
                $zipPath,
                ZipArchive::CREATE |
                ZipArchive::OVERWRITE
            );


        if ($result !== true) {

            throw new RuntimeException(
                'Tidak dapat membuat file ZIP.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Tambahkan Semua File
        |--------------------------------------------------------------------------
        */

        $files =
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $sourceDirectory,
                    \FilesystemIterator::SKIP_DOTS
                )
            );


        foreach ($files as $file) {

            if (!$file->isFile()) {

                continue;
            }


            $filePath =
                $file->getRealPath();


            $relativePath =
                substr(
                    $filePath,
                    strlen($sourceDirectory) + 1
                );


            $zip->addFile(
                $filePath,
                $relativePath
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Close ZIP
        |--------------------------------------------------------------------------
        */

        $zip->close();


        /*
        |--------------------------------------------------------------------------
        | Check ZIP
        |--------------------------------------------------------------------------
        */

        if (!file_exists($zipPath)) {

            throw new RuntimeException(
                'File ZIP tidak berhasil dibuat.'
            );
        }
    }

   public function runFromWeb(): string
    {
        $phpBinary = PHP_BINARY;

        $artisan = base_path('artisan');


        /*
        |--------------------------------------------------------------------------
        | Log Environment
        |--------------------------------------------------------------------------
        */

        \Log::info('RudyPOS Backup CLI', [
            'php_binary' => $phpBinary,
            'artisan' => $artisan,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Jalankan Artisan melalui PHP CLI
        |--------------------------------------------------------------------------
        */

        $process = new Process([
            $phpBinary,
            $artisan,
            'backup:run',
            '--no-interaction',
        ]);


        $process->setTimeout(600);


        /*
        |--------------------------------------------------------------------------
        | Jalankan
        |--------------------------------------------------------------------------
        */

        $process->run();


        /*
        |--------------------------------------------------------------------------
        | Ambil hasil proses
        |--------------------------------------------------------------------------
        */

        $output =
            trim($process->getOutput());

        $errorOutput =
            trim($process->getErrorOutput());

        $exitCode =
            $process->getExitCode();


        /*
        |--------------------------------------------------------------------------
        | Log hasil proses
        |--------------------------------------------------------------------------
        */

        \Log::info('RudyPOS Backup CLI Result', [
            'successful' =>
                $process->isSuccessful(),

            'exit_code' =>
                $exitCode,

            'output' =>
                $output,

            'error_output' =>
                $errorOutput,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Jika proses gagal
        |--------------------------------------------------------------------------
        */

        if (!$process->isSuccessful()) {

            $message =
                $errorOutput
                ?: $output
                ?: 'Proses backup Artisan gagal tanpa pesan error.';


            throw new \RuntimeException(
                $message .
                ' [Exit Code: ' .
                ($exitCode ?? 'unknown') .
                ']'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cari Path Backup dari Output
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/File:\s*(.+)/',
                $output,
                $matches
            )
        ) {

            return trim($matches[1]);
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback: Cari Backup Terbaru
        |--------------------------------------------------------------------------
        */

        $disk = Storage::disk(
            config('backup.disk', 'local')
        );


        $directory = config(
            'backup.directory',
            'backups'
        );


        $files =
            $disk->files($directory);


        $zipFiles =
            array_filter(
                $files,
                fn ($file) =>
                    str_ends_with(
                        strtolower($file),
                        '.zip'
                    )
            );


        if (empty($zipFiles)) {

            throw new \RuntimeException(
                'Backup berhasil dijalankan tetapi file ZIP tidak ditemukan.'
            );
        }


        usort(
            $zipFiles,
            fn ($a, $b) =>
                $disk->lastModified($b)
                <=>
                $disk->lastModified($a)
        );


        return $zipFiles[0];
    }

    public function createWeb(): string
    {
        $disk = Storage::disk(
            config('backup.disk', 'local')
        );

        $backupDirectory = config(
            'backup.directory',
            'backups'
        );

        if (!$backupDirectory) {
            throw new \RuntimeException(
                'Direktori backup belum dikonfigurasi.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Nama Backup
        |--------------------------------------------------------------------------
        */

        $timestamp =
            now()->format('Y-m-d_H-i-s');

        $backupName =
            'rudypos_backup_' . $timestamp;

        $temporaryDirectory =
            $backupDirectory .
            '/' .
            $backupName;


        /*
        |--------------------------------------------------------------------------
        | Buat Temporary Directory
        |--------------------------------------------------------------------------
        */

        $disk->makeDirectory(
            $temporaryDirectory
        );


        /*
        |--------------------------------------------------------------------------
        | Database SQL
        |--------------------------------------------------------------------------
        */

        $databaseFile =
            $disk->path(
                $temporaryDirectory .
                '/database.sql'
            );


        /*
        |--------------------------------------------------------------------------
        | Generate Database Dump
        |--------------------------------------------------------------------------
        */

        $this->generateDatabaseSql(
            $databaseFile
        );


        /*
        |--------------------------------------------------------------------------
        | Backup Storage
        |--------------------------------------------------------------------------
        */

        $this->backupStorage(
            $disk,
            $temporaryDirectory
        );


        /*
        |--------------------------------------------------------------------------
        | ZIP
        |--------------------------------------------------------------------------
        */

        $zipRelativePath =
            $backupDirectory .
            '/' .
            $backupName .
            '.zip';


        $zipPath =
            $disk->path(
                $zipRelativePath
            );


        $this->createZip(
            $disk->path($temporaryDirectory),
            $zipPath
        );


        /*
        |--------------------------------------------------------------------------
        | Hapus Temporary Folder
        |--------------------------------------------------------------------------
        */

        $disk->deleteDirectory(
            $temporaryDirectory
        );


        /*
        |--------------------------------------------------------------------------
        | Validasi ZIP
        |--------------------------------------------------------------------------
        */

        if (!file_exists($zipPath)) {

            throw new \RuntimeException(
                'File ZIP backup tidak berhasil dibuat.'
            );
        }


        return $zipRelativePath;
    }   
    
    protected function generateDatabaseSql(string $outputFile): void {

        $connection =
            \DB::connection();

        $databaseName =
            $connection->getDatabaseName();


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $sql = '';

        $sql .= "-- RudyPOS Database Backup\n";

        $sql .= "-- Database: {$databaseName}\n";

        $sql .= "-- Generated: " .
            now()->format('Y-m-d H:i:s') .
            "\n\n";


        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";

        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";

        $sql .= "SET AUTOCOMMIT=0;\n";

        $sql .= "START TRANSACTION;\n\n";


        /*
        |--------------------------------------------------------------------------
        | Ambil Semua Table
        |--------------------------------------------------------------------------
        */

        $tables =
            $connection->select(
                'SHOW FULL TABLES WHERE Table_type = "BASE TABLE"'
            );


        /*
        |--------------------------------------------------------------------------
        | Ambil Nama Kolom Table
        |--------------------------------------------------------------------------
        */

        foreach ($tables as $table) {

            $tableArray =
                (array) $table;


            $tableName =
                array_values(
                    $tableArray
                )[0];


            /*
            |--------------------------------------------------------------------------
            | DROP TABLE
            |--------------------------------------------------------------------------
            */

            $sql .=
                "DROP TABLE IF EXISTS " .
                $this->quoteIdentifier($tableName) .
                ";\n\n";


            /*
            |--------------------------------------------------------------------------
            | CREATE TABLE
            |--------------------------------------------------------------------------
            */

            $createResult =
                $connection->select(
                    "SHOW CREATE TABLE " .
                    $this->quoteIdentifier($tableName)
                );


            $createArray =
                (array) $createResult[0];


            $createSql =
                $createArray[
                    array_key_last($createArray)
                ];


            $sql .=
                $createSql .
                ";\n\n";


            /*
            |--------------------------------------------------------------------------
            | Ambil Data
            |--------------------------------------------------------------------------
            */

            $rows =
                $connection
                    ->table($tableName)
                    ->get();


            if ($rows->isEmpty()) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            $columns =
                array_keys(
                    (array) $rows->first()
                );


            $columnSql =
                implode(
                    ', ',
                    array_map(
                        fn ($column) =>
                            $this->quoteIdentifier(
                                $column
                            ),
                        $columns
                    )
                );


            $sql .=
                "INSERT INTO " .
                $this->quoteIdentifier($tableName) .
                " (" .
                $columnSql .
                ") VALUES\n";


            $valueRows = [];


            foreach ($rows as $row) {

                $values = [];


                foreach (
                    $columns as $column
                ) {

                    $value =
                        $row->{$column};


                    if ($value === null) {

                        $values[] =
                            'NULL';

                        continue;
                    }


                    $values[] =
                        $this->quoteValue(
                            $value
                        );
                }


                $valueRows[] =
                    '(' .
                    implode(
                        ', ',
                        $values
                    ) .
                    ')';
            }


            $sql .=
                implode(
                    ",\n",
                    $valueRows
                );


            $sql .= ";\n\n";
        }


        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        $sql .=
            "COMMIT;\n";

        $sql .=
            "SET FOREIGN_KEY_CHECKS=1;\n";


        /*
        |--------------------------------------------------------------------------
        | Simpan File
        |--------------------------------------------------------------------------
        */

        file_put_contents(
            $outputFile,
            $sql
        );


        if (
            !file_exists($outputFile) ||
            filesize($outputFile) === 0
        ) {

            throw new \RuntimeException(
                'database.sql gagal dibuat.'
            );
        }
    }   

    protected function quoteIdentifier(string $identifier): string {

        return '`' .
            str_replace(
                '`',
                '``',
                $identifier
            ) .
            '`';
    }

    protected function quoteValue(mixed $value): string {

        if ($value === null) {

            return 'NULL';
        }


        if (is_bool($value)) {

            return $value
                ? '1'
                : '0';
        }


        if (is_int($value) || is_float($value)) {

            return (string) $value;
        }


        return "'" .
            addslashes((string) $value) .
            "'";
    }    
}