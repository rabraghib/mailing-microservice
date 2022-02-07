<?php

namespace App\Tests;

use App\Entity\MailRequest;
use App\Interface\MailRequestStatus;

class StatusWebhookTest extends TestCase
{
    public function testWebhookCall(): void
    {
        $app = $this->getAppInstance();
        $em = $this->getEntityManager();
        $mailRequestObject = $this->createMailRequestObject('my-awesome-id')
            ->setIsSubmitted(true);
        $em->persist($mailRequestObject);
        $em->flush();
        $data = [
            'ID' => $mailRequestObject->getId(),
            'STATUS' => "DELIVERED"
        ];
        $res = $app->handle(
            $this->createRequest("POST","/status-webhook", body: json_encode($data))
        );
        $em->refresh($mailRequestObject);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals(
            MailRequestStatus::SENT,
            $mailRequestObject->getStatus()
        );
    }

}
