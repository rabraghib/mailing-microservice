<?php

namespace App;

use App\Config\Config;
use App\Config\ConfigInterface;
use Closure;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use JetBrains\PhpStorm\Pure;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;

class ContainerDefinitions
{
    #[Pure] public static function getAppConfigs(): Config
    {
        return new Config([
            'database' => [
                'url' => $_ENV['DATABASE_URL'] ?? '',
            ],
            'isDevMode' => ($_ENV['APP_ENV'] ?: 'dev') === 'dev',
            'displayErrorDetails' => ($_ENV['APP_ENV'] ?: 'dev') === 'dev',
            'logError'            => false,
            'logErrorDetails'     => false,
            'logger' => [
                'name' => 'mailing-microservice',
                'path' => (string) $_ENV['LOGGING_PATH'] ?: __DIR__ . '/../var/logs/app.log',
                'level' => Logger::DEBUG,
            ],
        ]);
    }
    public static function getLoggerDefinition() : Closure
    {
        return function (ContainerInterface $c): Logger {
            $configs = $c->get(ConfigInterface::class);

            $loggerConfigs = $configs->get('logger');
            $logger = new Logger($loggerConfigs['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerConfigs['path'], $loggerConfigs['level']);
            $logger->pushHandler($handler);

            return $logger;
        };
    }
    public static function getEntityManager(): Closure
    {
        return function (ContainerInterface $container) {
            $configs = $configs ?? $container->get(ConfigInterface::class);
            return EntityManager::create(
                $configs->get('database'),
                Setup::createAttributeMetadataConfiguration(
                    array(__DIR__.'/Entity'),
                    isDevMode: $configs->get('isDevMode')
                )
            );
        };
    }
}