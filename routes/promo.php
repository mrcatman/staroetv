<?php

$domains = ['teleport.staroetv.su', 'teleport-origin.staroetv.su'];
foreach ($domains as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', [\App\Http\Controllers\PromoController::class, 'index']);
        Route::any('{any}', function () {
            return redirect('/');
        })->where('any', '.*');
    });
}
