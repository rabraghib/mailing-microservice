<?php

namespace App\Controller;

use App\Controller;
use App\Entity\MailRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class SendEmailController extends Controller
{
    public function handler(): Response
    {
        $data = $this->getParsedBodyData();
        $errors = $this->validateData($data, [
            "sender" => v::stringType()->notBlank()->email()->length(1,255),
            "recipient" => v::stringType()->notBlank()->email()->length(1,255),
            "message" => v::stringType()->notBlank(),
            "priority" => v::nullable(v::number()),
        ],true);
        if (count($errors) > 0){
            return $this->respond(400, [
                "status" => "denied",
                "reason" => $errors
            ]);
        }
        $requestId = @$this->generateGUID();
        $mailRequest = (new MailRequest())
            ->setId($requestId)
            ->setSender($data['sender'])
            ->setRecipient($data['recipient'])
            ->setMessage($data['message'])
            ->setPriority($data['priority'] ?? 0);

        try {
            $isAccepted = $this->mailer->send($mailRequest);
            $mailRequest->setIsSubmitted($isAccepted);
        } catch (\Exception $e){
            $this->logger->error($e->getMessage());
        }

        $this->entityManager->persist($mailRequest);
        $this->entityManager->flush();

        return $this->respond(200,[
            "status" => "accepted",
            "request_id" => $requestId
        ]);
    }

    function generateGUID(): string
    {
        if (function_exists('com_create_guid'))
        {
            return trim(com_create_guid(),'{}');
        }
        else
        {
            mt_srand((double)microtime()*10000);
            $set_charid = strtoupper(md5(uniqid(rand(), true)));
            $set_hyphen = chr(45);
            $set_uuid = substr($set_charid, 0, 8).$set_hyphen
                .substr($set_charid, 8, 4).$set_hyphen
                .substr($set_charid,12, 4).$set_hyphen
                .substr($set_charid,16, 4).$set_hyphen
                .substr($set_charid,20,12);
            return $set_uuid;
        }
    }
}