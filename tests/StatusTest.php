<?php

namespace App\Tests;

class StatusTest extends TestCase
{
    public function testNotFound(): void
    {
        $app = $this->getAppInstance();
        $request = $this->createRequest("GET",'/status/not-exists');
        $res = $app->handle($request);
        $this->assertEquals(404, $res->getStatusCode(), "It should return 404 (Not found)");
    }
    protected function testGetRequestStatus(): void
    {
        $id = 'my-awesome-id';
        $app = $this->getAppInstance();
        $em = $this->getEntityManager();

        $mailRequestObject = $this->createMailRequestObject($id);
        $em->persist($mailRequestObject);
        $em->flush();
        $request = $this->createRequest("GET","/status/$id");

        $res = $app->handle($request);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals($mailRequestObject->getId(), json_decode($res->getBody(), true)['request_id']);
    }

}
