<?php

namespace Reinholdjesse\Backup\Livewire\Backup;

use Exception;
use Illuminate\Support\Carbon;
use Livewire\Component;
use mysqli;
use Reinholdjesse\Core\Models\Setting;

class Index extends Component
{
    public $liste = [];
    public $root = 'backups/';

    public $connection = false;
    public $connecting_result = null;

    public $DBhost;
    public $DBdatabase;
    public $DBuser;
    public $DBpassword;

    public function mount()
    {
        if ($result = $this->existsSettingsEntry('backup.host')) {
            $this->DBhost = $result->value;
        } else {
            $this->createBackupSettingsEntry('backup.host', 'Backup Host');
            $this->DBhost = null;
        }

        if ($result = $this->existsSettingsEntry('backup.database')) {
            $this->DBdatabase = $result->value;
        } else {
            $this->createBackupSettingsEntry('backup.database', 'Backup Datenbank');
            $this->DBdatabase = null;
        }

        if ($result = $this->existsSettingsEntry('backup.user')) {
            $this->DBuser = $result->value;
        } else {
            $this->createBackupSettingsEntry('backup.user', 'Backup User');
            $this->DBuser = null;
        }

        if ($result = $this->existsSettingsEntry('backup.password')) {
            $this->DBpassword = $result->value;
        } else {
            $this->createBackupSettingsEntry('backup.password', 'Backup Password');
            $this->DBpassword = null;
        }

        $this->connection = $this->dbConnecting();
    }

    public function render()
    {
        $this->load();
        return view('backup::livewire.backup.index')->layout('component::layouts.dashboard');
    }

    public function load()
    {
        $this->liste = [];

        if (!$this->folderExists()) {
            return;
        }

        foreach (scandir($this->getStoreFolder($this->root)) as $value) {
            if ($value != '.' && $value != '..') {
                $split = explode('_', $value);

                $date = $split[0];
                $time = str_replace(['-', '.sql.zip'], [':', ''], $split[1]);
                $temp = [
                    'path' => $value,
                    'created_at' => Carbon::parse($date . ' ' . $time)->format('d.m.Y H:i:s'),
                    'size' => $this->getFileSize($this->getStoreFolder($this->root . $value)),
                ];

                $this->liste[] = $temp;
            }
        }
    }

    public function createBackup()
    {
        if (!$this->folderExists()) {
            $this->createFolder();
        }

        $filename = $this->getStoreFolder($this->root . $this->getFilename()) . '.sql.zip';

        passthru("mysqldump --user=" . $this->DBuser . " --password=" . $this->DBpassword . " --host=" . $this->DBhost . " " . $this->DBdatabase . " | gzip -c  > " . $filename . " ", $result);

        //exec("mysqldump --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . env('DB_DATABASE') . " --result-file=" . $filename . ".sql.gz 2>&1", $result);
        //dd($result);
        if ($result == 255) {
            $this->dispatchBrowserEvent('banner-message', [
                'style' => 'danger',
                'message' => 'Fehler beim erstellt des Backups.',
            ]);
            return;
        }

        if (file_exists($filename) && $this->getFileSize($filename) != 0.0) {

            $this->dispatchBrowserEvent('banner-message', [
                'style' => 'success',
                'message' => 'Backup erfogreich erstellt',
            ]);
        } else {
            $this->deleteFile($filename);

            $this->dispatchBrowserEvent('banner-message', [
                'style' => 'danger',
                'message' => 'Fehler beim erstellt des Backups.',
            ]);
        }
    }

    public function delete(string $file)
    {
        if ($this->deleteFile($this->getStoreFolder($this->root . $file))) {
            $this->dispatchBrowserEvent('banner-message', [
                'style' => 'success',
                'message' => 'Backup erfogreich gelöscht',
            ]);
        } else {
            $this->dispatchBrowserEvent('banner-message', [
                'style' => 'danger',
                'message' => 'Fehler beim löschen des Backups.',
            ]);
        }
    }

    public function dbConnectingTest()
    {
        if ($this->dbConnecting()) {
            $this->connecting_result = true;
        } else {
            $this->connecting_result = false;
        }
    }

    public function settingsUpdate()
    {
        $this->update('backup.host', $this->DBhost);
        $this->update('backup.database', $this->DBdatabase);
        $this->update('backup.user', $this->DBuser);
        $this->update('backup.password', $this->DBpassword);
    }

    private function existsSettingsEntry(string $key)
    {
        return Setting::where('key', $key)->first();
    }

    private function createBackupSettingsEntry(string $key, string $title)
    {
        return Setting::insert([
            'key' => $key,
            'display_name' => $title,
            'type' => 'text',
            'group' => 'Backup',
        ]);
    }

    private function update(string $key, string $value)
    {
        Setting::where('key', $key)->update([
            'value' => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function dbConnecting()
    {
        if (!empty($this->DBhost) && !empty($this->DBdatabase) && !empty($this->DBuser) && !empty($this->DBpassword)) {
            try {
                $conn = new mysqli($this->DBhost, $this->DBuser, $this->DBpassword, $this->DBdatabase);

                // Check connection
                if ($conn->connect_error) {
                    return false;
                    //die("Connection failed: " . $conn->connect_error);
                }
                return true;

            } catch (Exception $e) {
                unset($e);
                //dd($e);
            }
        }
    }

    private function folderExists()
    {
        return file_exists(storage_path($this->root));
    }

    private function createFolder()
    {
        return mkdir(storage_path($this->root));
    }

    private function getFilename()
    {
        return date("Y-m-d_H-i-s");
    }
    private function getStoreFolder(string $path)
    {
        return storage_path($path);
    }
    private function getFileSize(string $file)
    {
        return round(filesize($file) / 1000000, 2);
    }

    private function deleteFile(string $file)
    {
        return unlink($file);
    }
}
