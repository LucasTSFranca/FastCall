<?php

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\ClientCallController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\QueueController;
use App\Http\Controllers\Dashboard\ReportController;
use App\Http\Controllers\Dashboard\TableController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

// ── Página pública do cliente via QR / NFC ───────────────────────────────────
Route::prefix('call/{qrToken}')->group(function () {
    Route::get('/', [ClientCallController::class, 'show'])->name('client.call.show');
    Route::post('/', [ClientCallController::class, 'store'])->name('client.call.store');
    Route::delete('/cancel', [ClientCallController::class, 'cancel'])->name('client.call.cancel');
    Route::get('/position/{callId}', [ClientCallController::class, 'position'])->name('client.call.position');
})->middleware('throttle:30,1');

// ── Autenticação ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Área autenticada (todos os perfis) ───────────────────────────────────────
Route::middleware(['auth', 'ensure.active'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Preferência de tema (persiste no banco)
    Route::post('/theme', [UserPreferenceController::class, 'updateTheme'])
        ->name('theme.update');

    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // ── Gerenciamento de Mesas (Gerente e Admin) ─────────────────────────────
    Route::middleware('role:gerente,administrador')->group(function () {
        Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
        Route::get('/tables/create', [TableController::class, 'create'])->name('tables.create');
        Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
        Route::get('/tables/{table}/edit', [TableController::class, 'edit'])->name('tables.edit');
        Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
        Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');
        Route::get('/tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');
        Route::post('/tables/{table}/toggle', [TableController::class, 'toggle'])->name('tables.toggle');
    });

    // ── Fila (Atendentes, Gerentes e Admin) ──────────────────────────────────
    Route::prefix('queue')->name('queue.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/live', [QueueController::class, 'live'])->name('live');
        Route::post('/{call}/attend', [QueueController::class, 'attend'])->name('attend');
        Route::post('/{call}/close', [QueueController::class, 'close'])->name('close');
        Route::post('/close-batch', [QueueController::class, 'closeBatch'])->name('close.batch');
        Route::delete('/clear-all', [QueueController::class, 'clearAll'])->name('clear.all');
    });
});

// ── Área Admin ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'ensure.active', 'role:administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('companies/{company}/toggle', [CompanyController::class, 'toggle'])->name('companies.toggle');
        Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::resource('companies', CompanyController::class);
        Route::resource('users', UserController::class);
    });

// Rota raiz
Route::get('/', [HomeController::class, 'index']);
