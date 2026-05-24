<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function database()
    {
        $connection = config('database.connections.mysql');
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir . '/backup_' . now()->format('Ymd_His') . '.sql';
        $mysqldump = env('MYSQLDUMP_PATH', 'mysqldump');

        $command = [
            $mysqldump,
            '-h' . $connection['host'],
            '-P' . $connection['port'],
            '-u' . $connection['username'],
        ];
        if ($connection['password'] !== '') {
            $command[] = '-p' . $connection['password'];
        }
        $command[] = $connection['database'];
        $command[] = '--result-file=' . $file;

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($file)) {
            return back()->withErrors('Не удалось создать резервную копию. Проверьте MYSQLDUMP_PATH в .env.');
        }

        Audit::log('database_backup_created', null, 'Создана резервная копия базы');

        return response()->download($file);
    }
}
