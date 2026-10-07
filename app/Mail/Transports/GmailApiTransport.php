<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Exception;

class GmailApiTransport extends AbstractTransport
{
    protected Client $client;
    protected Gmail $service;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->addScope(Gmail::GMAIL_SEND);

        // 1. Fetch the access token using your refresh token
        $refreshToken = config('services.google.refresh_token');
        $accessToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        // 2. Check if the token fetch was successful
        if (isset($accessToken['error'])) {
            throw new Exception('Gmail API Authentication failed: ' . $accessToken['error_description']);
        }

        // 3. Set the access token so the client includes it in API requests
        $this->client->setAccessToken($accessToken);

        $this->service = new Gmail($this->client);
    }

    /**
     * This is the method Laravel calls when it processes an outbound mail payload.
     */
    protected function doSend(SentMessage $message): void
    {
        // 2. Convert Laravel's clean Mailable object chain back into a valid RFC 2822 email string
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $rawMessageString = $email->toString();

        // 3. Encode the compiled payload into Google's strict web-safe base64 format
        $mimeSafeString = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($rawMessageString));

        try {
            $gmailMessage = new Message();
            $gmailMessage->setRaw($mimeSafeString);

            // 4. Broadcast the message over standard port 443 HTTPS REST traffic
            $this->service->users_messages->send('me', $gmailMessage);
        } catch (Exception $e) {
            throw new Exception('Gmail API Custom Driver failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'gmail_api';
    }
}