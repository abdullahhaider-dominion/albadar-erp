<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoznamchaController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /\n", 200, [
    'Content-Type' => 'text/plain; charset=UTF-8',
]));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/roznamcha', [RoznamchaController::class, 'index'])->name('roznamcha.index');
    Route::get('/roznamcha/print', [RoznamchaController::class, 'print'])->name('roznamcha.print');
    Route::get('/roznamcha/export/csv', [RoznamchaController::class, 'exportCsv'])->name('roznamcha.export.csv');
    Route::get('/roznamcha/export/excel', [RoznamchaController::class, 'exportExcel'])->name('roznamcha.export.excel');

    Route::get('/entries/income/create', [EntryController::class, 'createIncome'])->name('entries.income.create');
    Route::get('/entries/expense/create', [EntryController::class, 'createExpense'])->name('entries.expense.create');
    Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');
    Route::get('/entries/{entry}/edit', [EntryController::class, 'edit'])->name('entries.edit');
    Route::put('/entries/{entry}', [EntryController::class, 'update'])->name('entries.update');

    Route::middleware(EnsureAdmin::class)->group(function () {
        Route::get('/entries/{entry}/history', [EntryController::class, 'history'])->name('entries.history');
        Route::delete('/entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');
        Route::post('/entries/{id}/restore', [EntryController::class, 'restore'])->name('entries.restore');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');

        Route::get('/categories', [ExpenseCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [ExpenseCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [ExpenseCategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/{category}/toggle', [ExpenseCategoryController::class, 'toggle'])->name('categories.toggle');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    });
});
