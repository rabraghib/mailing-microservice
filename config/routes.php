<?php

use Slim\App;
use App\Controller\SendEmailController;
use App\Controller\StatusController;
use App\Controller\StatusWebhookController;

return function (App $app) {
    $app->post('/send', SendEmailController::class);
    $app->get('/status/{id}', StatusController::class);
    $app->post('/status-webhook', StatusWebhookController::class);
};