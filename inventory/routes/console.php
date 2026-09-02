<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\StockAlertService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('stock-alerts:sync {--notify : Send email notifications for newly opened alerts}', function (StockAlertService $service) {
    $created = $service->synchronize((bool) $this->option('notify'));

    $this->info("Stock alerts synchronized. {$created} new alert(s) opened.");
})->purpose('Synchronize persistent stock alerts');
