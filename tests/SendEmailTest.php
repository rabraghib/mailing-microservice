<?php

namespace App\Tests;

class SendEmailTest extends TestCase
{
    // TODO
    public function testSendRequestAndGetItsStatus(): void
    {
        $app = $this->getAppInstance();
        $mailRequestObject = $this->createMailRequestObject('non-used-id');
        $data = [
            'sender' => $mailRequestObject->getSender(),
            'recipient' => $mailRequestObject->getRecipient(),
            'message' => $mailRequestObject->getMessage()
        ];
        $request = $this->createRequest("POST",'/send', body: json_encode($data));
        $res = $app->handle($request);
        $this->assertEquals(200, $res->getStatusCode());
    }
}
