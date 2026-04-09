<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;


class DatabaseDumpController extends Controller
{
    public function dump()
    {

        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        $sql = "";

        foreach ($tables as $tableObj) {
            $table = array_values((array)$tableObj)[0];

            // Dump schema
            $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
            $sql .= "DROP TABLE IF EXISTS `$table`;\n$create;\n\n";

            // Dump data
            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $columns = array_map(fn($col) => "`$col`", array_keys((array) $row));
                $values = array_map(fn($val) => is_null($val) ? 'NULL' : DB::getPdo()->quote($val), array_values((array) $row));

                $sql .= "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
            }

            $sql .= "\n\n";
        }

        $fileName = 'custom_backup_' . now()->format('Ymd_His') . '.sql';
        Storage::put($fileName, $sql);

        return response()->download(storage_path("app/{$fileName}"))->deleteFileAfterSend(true);
    }
}
