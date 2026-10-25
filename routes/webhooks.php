<?php

use App\Http\Controllers\Shop\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes (Payment & Third-party integrations)
|--------------------------------------------------------------------------
|
| Webhook routes for payment gateway (Midtrans) notifications.
| Excluded from CSRF verification in bootstrap/app.php.
|
*/

Route::post('/webhooks/midtrans', [WebhookController::class, 'handleMidtrans'])->name('webhook.midtrans');
