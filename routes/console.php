<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// El comando `sernatur:sync` se registra acá una vez que se implemente
// Services/Sernatur/SernaturSyncService (módulo actualmente inactivo).
