<?php

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel();

return ConsoleRunner::createHelperSet($kernel->container->get(EntityManagerInterface::class));