<?php

namespace Utopia\Messaging\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS as SMSAdapter;
use Utopia\Messaging\Messages\SMS as SMSMessage;
use Utopia\Messaging\Response;

// Reference Material
// https://www.seven.io/en/docs/gateway/http-api/sms-dispatch/
class Seven extends SMSAdapter
{
    protected const NAME = 'Seven';

    /**
     * @param  string  $apiKey Seven API token
     */
    public function __construct(
        private string $apiKey,
        private ?string $from = null
    ) {
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getMaxMessagesPerRequest(): int
    {
        return 1000;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function process(SMSMessage $message): array
    {
        $response = new Response($this->getType());

        $result = $this->request(
            method: 'POST',
            url: 'https://gateway.sms77.io/api/sms',
            headers: [
                'Authorization: Basic '.$this->apiKey,
                'content-type: application/json',
            ],
            body: \json_encode([
                'from' => $this->from ?? $message->getFrom(),
                'to' => \implode(',', $message->getTo()),
                'text' => $message->getContent(),
            ]),
        );

        if ($result['statusCode'] >= 200 && $result['statusCode'] < 300) {
            $response->setDeliveredTo(\count($message->getTo()));
            foreach ($message->getTo() as $to) {
                $response->addResultForRecipient($to);
            }
        } else {
            foreach ($message->getTo() as $to) {
                $response->addResultForRecipient($to, 'Unknown error.');
            }
        }

        return $response->toArray();
    }
}
