<?php

namespace App;

use App\Config\ConfigInterface;
use App\Controller\ErrorHandlerController;
use App\Entity\MailRequest;
use App\Helper\CommandLineHelper as CLI;
use App\Helper\MailingHelper;
use Closure;
use Codedungeon\PHPCliColors\Color;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Dotenv\Dotenv;
use Exception;
use JetBrains\PhpStorm\NoReturn;
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

    public function getApp(): App
    {
        return $this->app;
    }

    #[NoReturn] public function runWorker(bool $isServeMode, bool $initialCall = true)
    {
        $workerPeriodSeconds = (int) $_ENV['WORKER_PERIOD_SECONDS'];
        if ($initialCall){
            echo PHP_EOL;
            CLI::logColored('Submit emails for delivery Worker is running...',Color::CYAN);
            echo PHP_EOL;
            if (!$workerPeriodSeconds && $isServeMode) {
                CLI::logError("env variable WORKER_PERIOD_SECONDS required to be set in serve mode" . PHP_EOL);
                exit(1);
            }
            if ($isServeMode){
                CLI::logSuccess("Serve mode is enabled!");
                CLI::logInfo("Checking & submitting queued emails every ${workerPeriodSeconds} seconds.");
                echo PHP_EOL;
            }
        }
        $em = $this->container->get(EntityManagerInterface::class);
        $repository = $em->getRepository(MailRequest::class);
        $mailer = $this->container->get(MailingHelper::class);
        $mailRequests = [];
        CLI::logColored(CLI::DASH_SEPARATOR,Color::CYAN);
        try {
            $mailRequests = $repository->findBy([
                'isSubmitted' => false
            ],[
                'priority' => 'DESC'
            ]);
        } catch(Exception $e){
            CLI::logError('Error: '.$e->getMessage());
        }

        $totalCount = count($mailRequests);
        $acceptedCount = 0;
        CLI::logInfo("Found ${totalCount} queued emails for delivery.");
        foreach ($mailRequests as $mailRequest){
            try {
                $isAccepted = $mailer->send($mailRequest);
                if ($isAccepted) {
                    $mailRequest->setIsSubmitted(true);
                    $acceptedCount++;
                }
            } catch(Exception $e){
                // Don't throw error and retry in the next run
            }
        }
        $em->flush();
        if ($acceptedCount > 0){
            CLI::logSuccess("${acceptedCount} email successfully submitted for delivery!");
        }
        if ($totalCount > $acceptedCount) {
            $failedCount = $totalCount - $acceptedCount;
            CLI::logInfo("${failedCount} email failed submitting!They will stay queued for the next run.");
        }
        CLI::logColored(CLI::DASH_SEPARATOR,Color::CYAN);
        echo PHP_EOL;
        if ($isServeMode) {
            echo PHP_EOL;
            for ($i = $workerPeriodSeconds; $i > 0; $i--){
                CLI::logInfo("The next run is after $i seconds.",false);
                sleep(1);
                echo CLI::CLEAR_LINE;
            }
            $this->runWorker(true,false);
        }
        exit(0);
    }

    /**
     * @throws Exception
     */
    protected function buildContainer(): Container
    {
        $containerBuilder = new ContainerBuilder();
        // TODO: fix why this is causing error
        // $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
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
