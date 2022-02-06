<?php

namespace App\Controller;

use App\Controller;
use App\Entity\MailRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

class StatusController extends Controller
{
    public function handler(): Response
    {
        $requestId = $this->resolveArg('id');
        /** @var MailRequest $request */
        $request = $this->getRepository(MailRequest::class)->find($requestId);
        if (!isset($request)){
            throw new HttpNotFoundException($this->request,"Request with id \"$requestId\" not found.");
        }
        return $this->respond(
            data: [
                'request_id' => $request->getId(),
                'status' => $request->getStatus(),
                'priority' => $request->getPriority()
            ]
        );
    }
}