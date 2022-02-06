<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;
use Throwable;

class ErrorHandlerController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger){

        $this->logger = $logger;
    }

    public function __invoke (
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response
    {
        $this->logger->error($exception->getMessage());
        $statusCode = $this->determineStatusCode($request->getMethod(), $exception);
        $payload = [
            'statusCode' => $statusCode,
            'error' => $exception->getMessage()
        ];
        $response = new Response($statusCode);
        $response->getBody()->write(
            (string) json_encode($payload, JSON_PRETTY_PRINT)
        );
        return $response->withHeader('Content-Type', 'application/json');
    }

    protected function determineStatusCode(string $method, $exception): int
    {
        if ($method === 'OPTIONS') {
            return 200;
        }
        if ($exception instanceof HttpException) {
            return $exception->getCode();
        }
        return 500;
    }
}