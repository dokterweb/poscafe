<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupController extends Controller
{
    /**
     * Menampilkan halaman Backup & Restore.
     */
    public function index()
    {
        $backups = $this->getBackups();

        return view('backup.index', compact('backups'));
    }


  public function create(BackupService $backupService)
{
    try {

        $path =
            $backupService->createWeb();


        return response()->json([

            'success' => true,

            'message' =>
                'Backup berhasil dibuat.',

            'backup' =>
                $this->formatBackup($path),

        ]);

    } catch (Throwable $e) {

        report($e);


        return response()->json([

            'success' => false,

            'message' =>
                'Backup gagal dibuat.',

            'error' =>
                $e->getMessage(),

        ], 500);
    }
}


    /**
     * Download backup.
     */
    public function download(string $filename)
    {
        $disk = Storage::disk(
            config('backup.disk', 'local')
        );

        $directory = config(
            'backup.directory',
            'backups'
        );

        /*
        |--------------------------------------------------------------------------
        | Sanitasi Filename
        |--------------------------------------------------------------------------
        */

        $filename = basename($filename);

        $path = $directory . '/' . $filename;


        /*
        |--------------------------------------------------------------------------
        | Cek File
        |--------------------------------------------------------------------------
        */

        if (!$disk->exists($path)) {

            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Download
        |--------------------------------------------------------------------------
        */

        return $disk->download(
            $path,
            $filename
        );
    }


    /**
     * Delete backup.
     */
    public function destroy(string $filename)
    {
        $disk = Storage::disk(
            config('backup.disk', 'local')
        );

        $directory = config(
            'backup.directory',
            'backups'
        );


        $filename = basename($filename);

        $path = $directory . '/' . $filename;


        if (!$disk->exists($path)) {

            return response()->json([
                'success' => false,
                'message' => 'File backup tidak ditemukan.',
            ], 404);
        }


        $disk->delete($path);


        return response()->json([
            'success' => true,
            'message' => 'Backup berhasil dihapus.',
        ]);
    }


    /**
     * Ambil daftar backup.
     */
    protected function getBackups(): array
    {
        $disk = Storage::disk(
            config('backup.disk', 'local')
        );

        $directory = config(
            'backup.directory',
            'backups'
        );


        if (!$disk->exists($directory)) {

            return [];
        }


        $files = $disk->files($directory);


        $backups = [];


        foreach ($files as $file) {

            if (
                !str_ends_with(
                    strtolower($file),
                    '.zip'
                )
            ) {
                continue;
            }


            $backups[] =
                $this->formatBackup($file);
        }


        /*
        |--------------------------------------------------------------------------
        | Terbaru di atas
        |--------------------------------------------------------------------------
        */

        usort(
            $backups,
            function ($a, $b) {

                return $b['timestamp']
                    <=> $a['timestamp'];
            }
        );


        return $backups;
    }


    protected function formatBackup(string $path): array {

        $disk = Storage::disk(
            config('backup.disk', 'local')
        );


        $filename = basename($path);


        /*
        |--------------------------------------------------------------------------
        | Pastikan File Ada
        |--------------------------------------------------------------------------
        */

        if (!$disk->exists($path)) {

            throw new \RuntimeException(
                'File backup tidak ditemukan: ' . $path
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil Metadata
        |--------------------------------------------------------------------------
        */

        $size =
            $disk->size($path);

        $timestamp =
            $disk->lastModified($path);


        return [

            'filename' => $filename,

            'name' => $filename,

            'size' =>
                $this->formatBytes($size),

            'timestamp' =>
                $timestamp,

            'date' =>
                date(
                    'd-m-Y H:i:s',
                    $timestamp
                ),
        ];
    }


    /**
     * Format ukuran file.
     */
    protected function formatBytes(
        int $bytes
    ): string {

        if ($bytes <= 0) {

            return '0 B';
        }


        $units = ['B','KB','MB','GB',];


        $index =
            floor(
                log($bytes, 1024)
            );


        $index =
            min(
                $index,
                count($units) - 1
            );


        return round(
            $bytes / pow(1024, $index),
            2
        ) . ' ' . $units[$index];
    }
}