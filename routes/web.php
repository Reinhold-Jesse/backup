<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('dashboard')->name('package.backup.')->group(function () {
    Route::get('backup', \Reinholdjesse\Backup\Livewire\Backup\Index::class)->name('index');
    Route::get('backup/{path}', [\Reinholdjesse\Backup\Controllers\BackupController::class, 'download'])->name('download');
    Route::get('backup/import/{path}', [\Reinholdjesse\Backup\Controllers\BackupController::class, 'import'])->name('import');
});
