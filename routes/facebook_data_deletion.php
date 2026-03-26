<?php

use Illuminate\Support\Facades\Route;
use Lartisan\FacebookDataDeletion\Http\Controllers\FacebookDataDeletionController;

if (! config('facebook-data-deletion.route.enabled', true)) {
    return;
}

$namePrefix = trim((string) config('facebook-data-deletion.route.name_prefix', 'facebook-data-deletion'), '.');

Route::middleware(config('facebook-data-deletion.route.middleware', ['api']))
    ->prefix((string) config('facebook-data-deletion.route.prefix', 'facebook'))
    ->as($namePrefix !== '' ? $namePrefix.'.' : '')
    ->group(function () {
        Route::post(
            (string) config('facebook-data-deletion.route.callback_path', 'data-deletion'),
            [FacebookDataDeletionController::class, 'handle'],
        )->name('handle');

        Route::get(
            (string) config('facebook-data-deletion.route.status_path', 'data-deletion/{confirmationCode}'),
            [FacebookDataDeletionController::class, 'status'],
        )->name('status');
    });
