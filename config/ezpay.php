<?php
return [
    /**
     * 金流相關參數
     * 付款及查詢時用以下的參數
     */
    'mpg' => [
        'MerchantId' => env('EZPAY_MPG_MERCHANT_ID', ''),
        'HashKey' => env('EZPAY_MPG_HASH_KEY', ''),
        'HashIV' => env('EZPAY_MPG_HASH_IV', '')
    ],
    /**
     * 開立電子發票參數
     */
    'invoice' => [
        'MerchantId' => env('EZPAY_INV_MERCHANT_ID', ''),
        'HashKey' => env('EZPAY_INV_HASH_KEY', ''),
        'HashIV' => env('EZPAY_INV_HASH_IV', '')
    ]
];