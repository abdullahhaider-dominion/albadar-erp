<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'companyName' => Setting::companyName(),
            'allowEditorEdit' => Setting::allowEditorEdit(),
            'lastBackupAt' => Setting::get('last_backup_at'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'allow_editor_edit' => ['nullable', 'boolean'],
        ]);

        Setting::put('company_name', $data['company_name']);
        Setting::put('allow_editor_edit', $request->boolean('allow_editor_edit') ? '1' : '0');

        AuditLogger::activity('settings_updated', Setting::class, null, [
            'company_name' => $data['company_name'],
            'allow_editor_edit' => $request->boolean('allow_editor_edit'),
        ], $request);

        return back()->with('success', 'Settings saved successfully.');
    }

    public function backup(): BinaryFileResponse|RedirectResponse
    {
        $connection = config('database.default');
        $stamp = now()->format('Y-m-d-His');
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        if ($connection === 'sqlite') {
            $path = database_path('database.sqlite');

            if (! File::exists($path)) {
                return back()->withErrors(['backup' => 'SQLite database file not found.']);
            }

            $filename = 'albadar-backup-'.$stamp.'.sqlite';
            $temp = $backupDir.DIRECTORY_SEPARATOR.$filename;
            File::copy($path, $temp);

            Setting::put('last_backup_at', now()->toDateTimeString());
            AuditLogger::activity('database_backup', null, null, ['file' => $filename]);

            return response()->download($temp, $filename)->deleteFileAfterSend(true);
        }

        try {
            $database = (string) config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_'.$database;
            $sql = "-- Al Badar ERP backup\n-- ".$stamp."\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $name = (string) $table->$key;

                if (! preg_match('/^[A-Za-z0-9_]+$/', $name)) {
                    continue;
                }

                $create = DB::select('SHOW CREATE TABLE `'.$name.'`')[0]->{'Create Table'};
                $sql .= "DROP TABLE IF EXISTS `{$name}`;\n{$create};\n\n";

                $rows = DB::table($name)->get();
                foreach ($rows as $row) {
                    $values = array_map(function ($v) {
                        if (is_null($v)) {
                            return 'NULL';
                        }

                        return "'".str_replace(["\\", "'"], ["\\\\", "''"], (string) $v)."'";
                    }, (array) $row);

                    $sql .= "INSERT INTO `{$name}` VALUES (".implode(',', $values).");\n";
                }
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $filename = 'albadar-backup-'.$stamp.'.sql';
            $temp = $backupDir.DIRECTORY_SEPARATOR.$filename;
            File::put($temp, $sql);

            Setting::put('last_backup_at', now()->toDateTimeString());
            AuditLogger::activity('database_backup', null, null, ['file' => $filename]);

            return response()->download($temp, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => 'Backup failed. Please try again or contact support.']);
        }
    }
}
