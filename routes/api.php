<?php

use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\OverviewController;
use App\Http\Controllers\Admin\ServiceRequestController as AdminServiceRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\N8nWebhookController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\StaffBotController;
use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    $authThrottle = (string) config('auth.api_throttle_per_minute');

    Route::post('register', [AuthController::class, 'register'])->middleware("throttle:{$authThrottle},1");
    Route::post('login', [AuthController::class, 'login'])->middleware("throttle:{$authThrottle},1");
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('requests', ServiceRequestController::class)
        ->parameters(['requests' => 'service_request'])
        ->only(['index', 'store', 'show']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('overview', OverviewController::class);
    Route::get('clients', [AdminClientController::class, 'index']);
    Route::apiResource('employees', AdminEmployeeController::class);
    Route::get('requests', [AdminServiceRequestController::class, 'index']);
    Route::get('requests/{service_request}', [AdminServiceRequestController::class, 'show']);
    Route::patch('requests/{service_request}', [AdminServiceRequestController::class, 'update']);
});

Route::post('webhooks/n8n', N8nWebhookController::class)
    ->middleware(['shared.secret:services.n8n.webhook_secret', 'throttle:60,1']);

Route::prefix('integrations')->middleware(['shared.secret:services.n8n.webhook_secret', 'throttle:60,1'])->group(function () {
    Route::post('odoo/quotation', [IntegrationController::class, 'quotation']);
    Route::post('odoo/invoice', [IntegrationController::class, 'invoice']);
    Route::post('clickup/tasks', [IntegrationController::class, 'tasks']);
    Route::post('telegram/notify', [IntegrationController::class, 'notify']);
});

Route::prefix('bot/telegram')->middleware('shared.secret:services.telegram.bot_secret')->group(function () {
    Route::post('link', [TelegramBotController::class, 'link']);
    Route::post('requests', [TelegramBotController::class, 'submit']);
});

Route::prefix('bot/staff')->middleware('shared.secret:services.telegram.staff_bot_secret')->group(function () {
    Route::get('me', [StaffBotController::class, 'me']);
    Route::post('reply', [StaffBotController::class, 'reply']);
});
