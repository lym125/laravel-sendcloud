<?php

namespace Lym125\SendCloud\Transport;

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;
use Lym125\SendCloud\SendCloudException;
use Swift_Attachment;
use Swift_Mime_SimpleMessage;

class SendCloudTransport extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The SendCloud API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The SendCloud API user.
     *
     * @var string
     */
    protected $user;

    /**
     * The SendCloud API endpoint.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create a new SendCloud transport instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string $key
     * @param string $user
     * @param string|null $endpoint
     * @return void
     */
    public function __construct(ClientInterface $client, $key, $user, $endpoint = null)
    {
        $this->key = $key;
        $this->user = $user;
        $this->client = $client;
        $this->endpoint = $endpoint ?? 'api.sendcloud.net';
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $this->sendRawEmail($message);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Send the SendCloud message
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @return array
     *
     * @throws \Lym125\SendCloud\SendCloudException
     */
    protected function sendRawEmail(Swift_Mime_SimpleMessage $message): array
    {
        $response = $this->client->request(
            'POST',
            "https://{$this->endpoint}/apiv2/mail/send",
            $this->payload($message)
        );

        $result = json_decode($response->getBody()->getContents(), true);

        if ((int) Arr::get($result, 'statusCode', 0) === 200) {
            return $result;
        }

        throw new SendCloudException(
            Arr::get($result, 'message', 'SendCloud Server Error'),
            Arr::get($result, 'statusCode', 0)
        );
    }

    /**
     * Get the HTTP payload for sending the SendCloud message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @return array
     */
    protected function payload(Swift_Mime_SimpleMessage $message): array
    {
        $parameters = [
            [
                'name' => 'apiUser',
                'contents' => $this->user,
            ],
            [
                'name' => 'apiKey',
                'contents' => $this->key,
            ],
            [
                'name' => 'from',
                'contents' => key($message->getFrom()),
            ],
            [
                'name' => 'fromName',
                'contents' => Arr::first($message->getFrom()),
            ],
            [
                'name' => 'to',
                'contents' => $this->addressesToString((array) $message->getTo()),
            ],
            [
                'name' => 'cc',
                'contents' => $this->addressesToString((array) $message->getCc()),
            ],
            [
                'name' => 'bcc',
                'contents' => $this->addressesToString((array) $message->getBcc()),
            ],
            [
                'name' => 'subject',
                'contents' => $message->getSubject(),
            ],
            [
                'name' => $message->getContentType() === 'text/plain' ? 'plain' : 'html',
                'contents' => $message->getBody(),
            ],
            [
                'name' => 'replyTo',
                'contents' => $this->addressesToString((array) $message->getReplyTo()),
            ],
        ];

        foreach ($message->getChildren() as $attachment) {
            if ($attachment instanceof Swift_Attachment) {
                $parameters[] = [
                    'name' => 'attachments',
                    'contents' => $attachment->getBody(),
                    'filename' => $attachment->getFilename(),
                ];
            }
        }

        return [
            'multipart' => $parameters,
        ];
    }

    /**
     * Convert the given addresses to a string.
     *
     * @param array $addresses
     * @return string
     */
    protected function addressesToString(array $addresses): string
    {
        return collect($addresses)->map(function ($display, $address) {
            return $display ? "{$display} <{$address}>" : $address;
        })->values()->implode(';');
    }
}
