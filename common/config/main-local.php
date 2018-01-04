<?php
return [
    'components' => [
        /*'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        /*'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.1.44;dbname=boom',
            'username' => 'boomvideo1',
            'password' => 'Yahoo@444',
            'charset' => 'utf8',
        ],*/
        /*'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=boom_ver1', //boomvideo
            'username' => 'root',
            'password' => 'H93BMDJrCWYRYE8J',
            'charset' => 'utf8',
        ],*/
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=boomcollective.csjdzy41kbcn.us-west-2.rds.amazonaws.com;dbname=boomvideo', //boomvideo
            'username' => 'boomvideo',
            'password' => 'G9J4g00xiMSFakXQ',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            'schemaCache' => 'cache',
            'enableQueryCache' => true,
            'queryCache' => 'redis',
            'queryCacheDuration' => 3600,
        ],
        /*'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=23.21.226.54;dbname=boom',
            'username' => 'suntec',
            'password' => 'Google@123',
            'charset' => 'utf8',
        ],*/
        /*'mailer' => [
             'class' => 'yii\swiftmailer\Mailer',
             //'viewPath' => '@common/mail',
             // send all mails to a file by default. You have to set
             // 'useFileTransport' to false and configure a transport
             // for the mailer to send real emails.
             'useFileTransport' => false,
             'transport' => [
             'class' => 'Swift_SmtpTransport',
             'host' => 'smtp.gmail.com',
             'username' => 'noreply.boom@gmail.com',
             'password' => 'boomvideo1234',
             'port' => '587',
             'encryption' => 'tls',
             ],
         ],*/
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'info@boomvideo.com.au',
                'password' => 'Boom1234',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
        'image' => [
            'class' => 'yii\image\ImageDriver',
            'driver' => 'GD',  //GD or Imagick
        ],
    ],
];