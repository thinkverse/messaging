<?php

namespace Utopia\Messaging\Adapter;

use Utopia\Messaging\Adapter;
use Utopia\Messaging\Messages\SMS as SMSMessage;

abstract class SMS extends Adapter
{
    private const TYPE = 'sms';
    private const MESSAGE_TYPE = SMSMessage::class;

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getMessageType(): string
    {
        return self::MESSAGE_TYPE;
    }

    /**
     * Send an SMS message.
     *
     * @param  SMSMessage  $message Message to send.
     * @return array{deliveredTo: int, type: string, results: array<array<string, mixed>>}
     *
     * @throws \Exception If the message fails.
     */
    abstract protected function process(SMSMessage $message): array;
}
