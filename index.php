<?php
global $app;
require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\RoutingServiceProvider());

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/routes.php';

$app->run();