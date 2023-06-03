<?php
return [
    // 微信
    'Weixin' => [
        'appid' => 'wx1db4af9dd7f6371f',
        'appsecret' => '9aa5ad7b6b2f6d221a83769ab8c14ea9',
        'merchantId ' => '1643243881', // 商户id
        'serialNo' => '34A07035CA76B3A82927EE23FD31FED4F747854F', // 证数序列号
        'APIv3' => 'e10adc3949ba59abbe56e057f20f883e',
    ],
//    'Weixin' => [
//        'appid' => 'wxdcf354de383af42c',
//        'appsecret' => '4395772d88f8aaf37691d2a7255e9f20',
//        'merchantId ' => '1645854384', // 商户id
//        'serialNo' => '2325BC2A09DAE28DF6A4ED6DA51DA692C455291A', // 证数序列号
//        'APIv3' => 'e10adc3949ba59abbe56e057f20f883e',
//    ],
    // 验证码
    'Code' => [
        'account' => 'YZM9536443', // 短信验证码
        'password' => 'TCr1v312qq1cd7',// 短信验证码
        'Marke_account'=>'M9073072',
        'Marke_password'=>'CfI7efL5j1f190',
        'appId' => 'N7LIDXwL', // 手机号码有查询
        'appKey' => 'NoJo2VBT' // 手机号码有查询
    ],
    // 外呼
    'phone' => [
        'accesskey' => 'cheshoubang',
        'appSecret' => 'C71180F210F8B59C82CE13C1D0F1242E',
        'url' => 'http://open.goodsalescloud.com',
        'TelX' => '03512929459',
        'NotifyUrl'=>'http://h.199909.xyz/'
    ],
    'Order' => [
        'Close' => 5 * 60,// 关闭交易时间  单位 s
        'Success' => 24 * 60 * 60  // 交易成功   单位  s
    ]
];