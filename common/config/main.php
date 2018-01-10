<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => [
        'queue',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                //'hostname' => 'artist-redis.nptsry.clustercfg.usw2.cache.amazonaws.com',
                'hostname' => '172.31.21.26',
                'port' => 6379,
                'database' => 0,
            ]
        ],
        'queue' => [
            'class' => '\yii\queue\redis\Queue',
            'as log' => '\yii\queue\LogBehavior',
            'redis' => 'redis',
            //'channel' => 'queue',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            //'showScriptName' => false,
            'enableStrictParsing' => false,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host' => 'smtp.zoho.com',
            'username' => 'rosemary@e2logy.com',
            'password' => '123@Google',
            'port' => '465',
            'encryption' => 'ssl',
            ],
        ],
        'i18n' => [
        'translations' => [
            'api*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => dirname(dirname(__DIR__)) . '/common/messages'
            ],
            'api_ver1*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => dirname(dirname(__DIR__)) . '/common/messages'
            ],
        ],
    ],
    ],
];
