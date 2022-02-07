<?php

namespace App\Tests;

use App\Entity\MailRequest;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class TestCase extends PHPUnit_TestCase
{
    protected function setUp(): void
    {
        $em = $this->getEntityManager();
        $tool = new SchemaTool($em);
        $classes = [
            $em->getClassMetadata(MailRequest::class)
        ];
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->getAppInstance()->getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        $routes = require __DIR__ . '/../config/routes.php';
        $kernel = new Kernel(SlimAppCallback: $routes);

        return $kernel->getApp();
    }


    protected function createMailRequestObject(string $id): MailRequest
    {
        return (new MailRequest())->setId($id)
            ->setSender('user@example.com')
            ->setRecipient('user@example.com')
            ->setMessage('Hello world!');
    }

    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['Content-Type' => 'application/json'],
        array $cookies = [],
        array $serverParams = [],
        string $body = ""
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, $body);
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }
        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}