<?php

use App\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$routes = require __DIR__ . '/../config/routes.php';

$kernel = new Kernel(SlimAppCallback: $routes);

$kernel->getApp()->run();