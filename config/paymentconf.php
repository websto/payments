<?php

//'enable' = (boolean/int)TRUE/FALSE .

return [

    'Privat'=>[
        'enable' => 1,

        'public_key'=>'',  //storeId
        'private_key'=>'', //passwordStore

        'title'=>[

            'II'=>//Индентификатор оплата частями
            'Приват (Оплата частями)',

            'PP'=>//Идентификатор мгновенная рассрочка
            'Приват (Мгновенная расрочка)',

        ],
    ],

    'LiqPay'=>[
        'enable' => 1,

        'public_key'=>'',  //merchant_id
        'private_key'=>'', //merchant_sig

        'title'=>[
            'liqpay'=>
            'Оплата через liqpay',
        ],
    ],

    //https://api.blockchain.info/customer/signup

    'Bitcoin'=>[
        'enable' => 1,

        'private_key'=> '11', //xpub_address
        'public_key' => '11',   //api_key
        'secret'     => 'ds12dsfds@dfgIO0-ewrYw',

        'title'=>[
            'bitcoin'=>
            'Оплата через bitcoin',
        ],
    ],
];