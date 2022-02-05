<?php

namespace App\Helper;

use App\Entity\MailRequest;
use Error;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

class MailingHelper
{
    private GuzzleHttpClient $httpClient;
    private string $API_BASE;
    public function __construct()
    {
        $this->httpClient = new GuzzleHttpClient();
        $this->API_BASE = $_ENV['API_BASE'];
    }

    /**
     * @return bool whether the request is accepted
     * @throws Error|GuzzleException Failed to send email
     */
    public function send(MailRequest $mailRequest): bool
    {
        $response = $this->httpClient->post("{$this->API_BASE}/api/emails", [
            GuzzleRequestOptions::JSON => [
                'request_id' => $mailRequest->getId(),
                'sender' => $mailRequest->getSender(),
                'recipient' => $mailRequest->getRecipient(),
                'message' => $mailRequest->getMessage()
            ]
        ]);
        return $response->getStatusCode() === 200 && json_decode((string) $response->getBody(),true)['STATUS'] === 'ACCEPTED';
    }
}