<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('reclamdesign:info', function () {
    $this->info('ReclamDesign Modern este instalat. Ruleaza php artisan serve pentru pornire.');
})->purpose('Afiseaza informatii despre proiect');
