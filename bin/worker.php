#!/usr/bin/php
<?php

use App\Kernel;

require __DIR__ . '/../vendor/autoload.php';


error_reporting(0); // TODO change this

$kernel = new Kernel();
$workerCmd = new Commando\Command();
$workerCmd->setHelp('Submit emails for delivery Worker');

$workerCmd->option('s')
    ->aka('serve')
    ->describedAs('Enable serve mode.')
    ->boolean();

$isServeMode = $workerCmd['serve'] ?? false;
$kernel->runWorker($isServeMode);