<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

// BodyParsingMiddlewareを追加
$app->addBodyParsingMiddleware();

// ルーティング定義を読み込む
(require __DIR__ . '/../src/routes.php')($app);

$app->run(); 