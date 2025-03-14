<?php

return [

    'TransactionTypes' => [
        'Order' => 'order',
        'CancelOrder' => 'cancel_order',
        'ReturnOrder' => 'return_order',
        'Ads' => 'ads',
        'Withdraw' => 'withdraw'
    ],

    'Descriptions' => [
        'DepositOrder' => 'واریز مبلغ :Amount تومان بابت سفارش شماره :OrderId با کسر :CommissionAmount تومان بابت پورسانت سامانه',
        'CancelOrder' => 'برداشت مبلغ :Amount تومان بابت لغو سفارش شماره :OrderId ',
        'ReturnOrder' => 'برداشت مبلغ :Amount تومان بابت برگشت سفارش شماره :OrderId ',
    ]

];
