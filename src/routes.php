<?php

Route::prefix('ezpay')->group(function(){
    Route::post('notify', 'TsaiYiHua\EZPay\Http\Controllers\EZPayController@notifyUrl')
        ->name('ezpay.notify');
    Route::post('return', 'TsaiYiHua\EZPay\Http\Controllers\EZPayController@returnUrl')
        ->name('ezpay.return');
    Route::post('customer', 'TsaiYiHua\EZPay\Http\Controllers\EZPayController@customUrl')
        ->name('ezpay.customer');
});