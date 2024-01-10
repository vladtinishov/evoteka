<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$managerAccessExcepts = [
    'auth/*',
    'site/*',
    'product/index',
    'product/view',
    'order/create',
    'order/index',
    'order/view',
];

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'as tokenFilter' => [
        'class' => 'app\components\TokenMiddleware',
        'except' => ['auth/*', 'site/*',],
    ],
    'as adminFilter' => [
        'class' => 'app\components\AdminMiddleware',
        'except' => [
            ...$managerAccessExcepts,
            'user/view',
            'user/index',
            'order/update-status',
        ],
    ],
    'as managerFilter' => [
        'class' => 'app\components\ManagerMiddleware',
        'except' => $managerAccessExcepts,
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'D_JV4nq-CYcMv769prrJhiuhOy7aBrEh',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
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
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'auth/get-token' => 'auth/get-token',
                'auth/register' => 'auth/register',

                'products' => 'product/index',
                'products/create' => 'product/create',
                'products/<id:\d+>' => 'product/view',
                'products/<id:\d+>/update' => 'product/update',
                'products/<id:\d+>/delete' => 'product/delete',

                'orders' => 'order/index',
                'orders/create' => 'order/create',
                'orders/<id:\d+>' => 'order/view',
                'orders/<id:\d+>/update' => 'order/update',
                'orders/<id:\d+>/delete' => 'order/delete',
                'orders/<id:\d+>/update-status' => 'order/update-status',

                'users' => 'user/index',
                'users/create' => 'user/create',
                'users/<id:\d+>' => 'user/view',
                'users/<id:\d+>/update' => 'user/update',
                'users/<id:\d+>/delete' => 'user/delete',
                'site/docs' => 'site/docs',
                'site/json-schema' => 'site/json-schema',
            ],
        ],
    ],
    'params' => $params,
    'modules' => [
        'swagger' => [
            'class' => \ignatenkovnikita\swagger\Module::class,
            'path' => dirname(__DIR__) . '/views/swagger',
            'isDisable' => function () {
                return false;
            },
            'afterRender' => function ($content) {
                $content = str_replace('{{host}}', 'http://evoteka.com', $content);
                $content = str_replace('{{basePath}}', '/api/v1', $content);
                return $content;
            }
        ],
        // ... другие модули
    ],

];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
