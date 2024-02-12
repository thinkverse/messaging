<?php

namespace Utopia\Tests\Adapter\Email;

use Utopia\Messaging\Adapter\Email\Mailgun;
use Utopia\Messaging\Messages\Email;
use Utopia\Tests\Adapter\Base;

class MailgunTest extends Base
{
    public function testSendEmail(): void
    {
        $key = \getenv('MAILGUN_API_KEY');
        $domain = \getenv('MAILGUN_DOMAIN');

        $sender = new Mailgun(
            apiKey: $key,
            domain: $domain,
            isEU: false
        );

        $to = \getenv('TEST_EMAIL');
        $subject = 'Test Subject';
        $content = 'Test Content';
        $fromEmail = 'sender@'.$domain;

        $message = new Email(
            to: [$to],
            subject: $subject,
            content: $content,
            fromName: 'Test Sender',
            fromEmail: $fromEmail,
        );

        $response = $sender->send($message);

        $this->assertResponse($response);
    }
}
