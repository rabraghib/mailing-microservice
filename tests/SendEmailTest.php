<?php

namespace App\Tests;

class SendEmailTest extends TestCase
{
    public function testSendValidRequest(): void
    {
        $app = $this->getAppInstance();
        $mailRequestObject = $this->createMailRequestObject('non-used-id');
        $data = [
            'sender' => $mailRequestObject->getSender(),
            'recipient' => $mailRequestObject->getRecipient(),
            'message' => $mailRequestObject->getMessage()
        ];

        $res = $app->handle(
            $this->createRequest("POST",'/send', body: json_encode($data))
        );
        $resData = json_decode($res->getBody(), true);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertArrayHasKey("request_id",$resData);
        $this->assertEquals("accepted", $resData['status']);

        $data['priority'] = 2;
        $res2 = $app->handle(
            $this->createRequest("POST",'/send', body: json_encode($data))
        );
        $this->assertEquals(200, $res2->getStatusCode());
    }
    public function testSendInValidRequest(): void
    {
        $app = $this->getAppInstance();
        $data = [
            'sender' => "peep",
            'recipient' => "peep",
            'message' => ""
        ];
        $request = $this->createRequest("POST",'/send', body: json_encode($data));
        $res = $app->handle($request);
        $resData = json_decode($res->getBody(), true);
        $this->assertEquals(400, $res->getStatusCode());
        $this->assertArrayHasKey("reason",$resData);
        $this->assertEquals("denied", $resData['status']);
    }
}
