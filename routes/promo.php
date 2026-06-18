<?php

Route::domain('teleport.staroetv.su')->group(function () {
    Route::get('/', [\App\Http\Controllers\PromoController::class, 'index']);
    Route::any('{any}', function () {
        return redirect('/');
    })->where('any', '.*');
});
