<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

require_once( dirname(__FILE__) . '/../components/helper/Commonfunctions.php');

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),    
	'controllerNamespace' => 'api_ver2\controllers',	
    'bootstrap' => ['log'],	
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module'
        ],
		'v2' => [
			'basePath' => '@app/modules/v2',
			'class'=>'api\modules\v2\Module'
		],	
    ],
    'components' => [        
        'user' => [
            'identityClass' => 'api\models\User',
            'enableAutoLogin' => false,
			'loginUrl' => null
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
			'class' => 'yii\web\UrlManager',
            'rules' => [			
				[
                    'class' => 'yii\rest\UrlRule', 
                    'controller' => [
								'v2/post',
								'v1/country',	
								'user'
							],					
                    'tokens' => [
                        '{id}' => '<id:\\w+>'
                    ]
                    
                ],		
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',				
            ],        
        ],
    ],	
    'params' => $params,
];



