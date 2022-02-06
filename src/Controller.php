<?php

namespace App;

use App\Helper\MailingHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

abstract class Controller {
    protected Request $request;
    protected Response $response;
    protected array $args;

    public function __construct(
        protected LoggerInterface $logger,
        protected EntityManagerInterface $entityManager,
        protected MailingHelper $mailer
    ){}

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->handler();
        } catch (HttpNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function handler(): Response;

    protected function getParsedBodyData(): object|array
    {
        return $this->request->getParsedBody();
    }

    protected function validateData(array $data, array $validators, bool $allowBlank = false): array
    {
        $errors = [];
        foreach ($validators as $field => $validator) {
            $dataValue = array_key_exists($field,$data) ? $data[$field] : null;
            if(!$allowBlank && !v::notBlank()->validate($dataValue)){
                $errors[$field] = "missing $field field.";
            } elseif (!$validator->validate($dataValue)){
                $errors[$field] = "\"${dataValue}\" is an invalid value.";
            }
        }
        return $errors;
    }

    protected function getRepository(string $entityName): EntityRepository|ObjectRepository
    {
        return $this->entityManager->getRepository($entityName);
    }

    /**
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name): mixed
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    protected function respond(
        int $statusCode = 200,
        object|array $data = null
    ): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}