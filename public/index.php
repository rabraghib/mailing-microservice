<?php

use App\Kernel;
use Slim\App;
use App\Controller\StatusWebhookController;
use App\Controller\SendEmailController;
use App\Controller\StatusController;

require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel(SlimAppCallback: function (App $app) {
    $app->post('/send', SendEmailController::class);
    $app->get('/status/{id}', StatusController::class);
    $app->post('/status-webhook', StatusWebhookController::class);
});

$kernel->RunApp();