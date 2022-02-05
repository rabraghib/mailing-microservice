<?php

namespace App;

use App\Config\ConfigInterface;
use App\Controller\ErrorHandlerController;
use Closure;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Dotenv\Dotenv;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

class Kernel
{
    public Container $container;
    private App $app;

    /**
     * @throws Exception
     */
    public function __construct(Closure $SlimAppCallback = null)
    {
        $this->loadEnvVariables();
        $this->container = $this->buildContainer();
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();
        $this->configureErrorMiddleware();
        $this->app->addBodyParsingMiddleware();
        if (isset($SlimAppCallback)){
            $SlimAppCallback($this->app);
        }
    }

    public function RunApp()
    {
        $this->app->run();
    }

    /**
     * @throws Exception
     */
    protected function buildContainer(): Container
    {
        $containerBuilder = new ContainerBuilder();
//        $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
        $containerBuilder->addDefinitions([
            ConfigInterface::class => ContainerDefinitions::getAppConfigs(),
            LoggerInterface::class => ContainerDefinitions::getLoggerDefinition(),
            EntityManagerInterface::class => ContainerDefinitions::getEntityManager(),
        ]);
        return $containerBuilder->build();
    }

    /**
     * @throws DependencyException | NotFoundException
     */
    protected function configureErrorMiddleware() {
        /** @var ConfigInterface $configs */
        $configs = $this->container->get(ConfigInterface::class);
        $displayErrorDetails = $configs->get('displayErrorDetails');
        $logError = $configs->get('logError');
        $logErrorDetails = $configs->get('logErrorDetails');
        $this->app->addErrorMiddleware($displayErrorDetails,$logError,$logErrorDetails)
            ->setDefaultErrorHandler(ErrorHandlerController::class);
    }

    protected function loadEnvVariables() {
        $dotenv = Dotenv::createImmutable(paths: __DIR__.'/../', names: [
            '.env',
            '.env.local'
        ], shortCircuit: false);
        $dotenv->safeLoad();
    }

}