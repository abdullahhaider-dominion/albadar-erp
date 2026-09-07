<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'companyName' => Setting::companyName(),
            'allowEditorEdit' => Setting::allowEditorEdit(),
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

        return back()->with('success', 'Settings saved successfully.');
    }

    public function backup(): BinaryFileResponse|RedirectResponse
    {
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $path = database_path('database.sqlite');

            if (! File::exists($path)) {
                return back()->withErrors(['backup' => 'SQLite database file not found.']);
            }

            $name = 'roznamcha-backup-'.now()->format('Y-m-d-His').'.sqlite';

            return response()->download($path, $name);
        }

        // For MySQL: dump tables to a simple SQL file via PHP (no mysqldump dependency).
        try {
            $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $key = 'Tables_in_'.config('database.connections.mysql.database');
            $sql = "-- Roznamcha ERP backup\n-- ".now()->toDateTimeString()."\n\n";

            foreach ($tables as $table) {
                $name = $table->$key;
                $create = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$name}`")[0]->{'Create Table'};
                $sql .= "DROP TABLE IF EXISTS `{$name}`;\n{$create};\n\n";

                $rows = \Illuminate\Support\Facades\DB::table($name)->get();
                foreach ($rows as $row) {
                    $values = array_map(function ($v) {
                        if (is_null($v)) {
                            return 'NULL';
                        }

                        return "'".str_replace("'", "''", (string) $v)."'";
                    }, (array) $row);

                    $sql .= "INSERT INTO `{$name}` VALUES (".implode(',', $values).");\n";
                }
                $sql .= "\n";
            }

            $filename = 'roznamcha-backup-'.now()->format('Y-m-d-His').'.sql';
            $temp = storage_path('app/'.$filename);
            File::put($temp, $sql);

            return response()->download($temp, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Backup failed: '.$e->getMessage()]);
        }
    }
}
