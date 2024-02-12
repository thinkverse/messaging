<?php

namespace Utopia\Tests\Adapter\SMS;

use Utopia\Messaging\Adapter\SMS\Twilio;
use Utopia\Messaging\Messages\SMS;
use Utopia\Tests\Adapter\Base;

class TwilioTest extends Base
{
    /**
     * @throws \Exception
     */
    public function testSendSMS(): void
    {
        $sender = new Twilio(\getenv('TWILIO_ACCOUNT_SID'), \getenv('TWILIO_AUTH_TOKEN'));
        $to = [\getenv('TWILIO_TO')];
        $from = \getenv('TWILIO_FROM');

        $message = new SMS(
            to: $to,
            content: 'Test Content',
            from: $from
        );

        $response = $sender->send($message);

        $this->assertResponse($response);
    }
}
