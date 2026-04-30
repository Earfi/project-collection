<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('setLocale')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/locale/{locale}', function (Request $request, string $locale): RedirectResponse {
        if (in_array($locale, config('locales.supported', ['en', 'th']), true)) {
            $request->session()->put('locale', $locale);
        }

        return back();
    })
        ->whereIn('locale', config('locales.supported', ['en', 'th', 'ja', 'zh', 'ko']))
        ->name('locale.switch');

    Route::middleware('auth')->group(function () {
        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::get('/items/import-template', [ItemController::class, 'importTemplate'])->name('items.import.template');
        Route::post('/items/import', [ItemController::class, 'importExcel'])->name('items.import');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/items/bulk-delete', [ItemController::class, 'bulkDestroy'])->name('items.bulk-destroy');
    });
});
