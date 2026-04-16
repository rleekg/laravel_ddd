<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('When there is no enemy within, the enemies outside cannot hurt you.');
})->purpose('Display an inspiring quote');
