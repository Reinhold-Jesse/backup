<?php

namespace Reinholdjesse\Backup\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class BackupController extends Controller
{
    public $path = 'backups/';

    public function download(string $file)
    {
        $file = storage_path('backups/' . $file);
        if (file_exists($file)) {
            return Response::download($file);
        } else {
            echo 'File not found';
        }
    }

    public function import(string $file)
    {
        $file_path = $this->getStoreFolder($this->path . $file);
        if (file_exists($file_path)) {

            $sql = str_replace('.zip', '', $file);
            //dd($file);
            $zip = new ZipArchive();

            $zip = $zip->open($file_path);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                print_r(basename($stat['name']) . PHP_EOL);
            }

            // if ($zip->open($file_path)):
            //     $zip->extractTo('bilder-archive');
            //     //dd($zip);
            //     $content = $zip->getFromName($file);

            //     echo $content;

            //     $zip->close();
            // endif;

            // $path = '../database/sql/2508-stand.sql';
            // $result = DB::unprepared(file_get_contents($path));

            // dd($result);
        }

    }

    private function getStoreFolder(string $path)
    {
        return storage_path($path);
    }
}
