<?php

Route::post('payment', 'Websto\Payments\Payment@render')->middleware('web');
Route::get('payment_callback', 'Websto\Payments\Payment@payment_callback')->middleware('web');
