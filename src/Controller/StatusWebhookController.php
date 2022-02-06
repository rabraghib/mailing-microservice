<?php

namespace App\Controller;

use App\Controller;
use App\Entity\MailRequest;
use App\Interface\MailRequestStatus;
use Psr\Http\Message\ResponseInterface as Response;

class StatusWebhookController extends Controller
{
    protected function handler(): Response
    {
        $data = $this->getParsedBodyData();
        $requestId = (string) $data['ID'];
        $status = (string) $data['STATUS'];
        /** @var MailRequest $mailRequest */
        $mailRequest = $this->getRepository(MailRequest::class)
            ->find($requestId);
        if ($mailRequest === null) {
            $this->logger->error("webhook received for no existing mail request with id \"$requestId\"");
        } else {
            if (!$mailRequest->isSubmitted()) {
                $this->logger->notice("isSubmitted field has an incorrect value for mail request with id \"$requestId\"");
                $mailRequest->setIsSubmitted(true);
            }
            $mailRequest->setStatus(
                $status === 'DELIVERED' ? MailRequestStatus::SENT : MailRequestStatus::FAILED
            );
            $this->entityManager->persist($mailRequest);
            $this->entityManager->flush();
        }

        return $this->respond(200, [
            'statusCode' => 200,
            'message' => 'webhook received successfully'
        ]);
    }
}