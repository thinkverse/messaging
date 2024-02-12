<?php

namespace Utopia\Messaging\Adapter\Email;

use PHPMailer\PHPMailer\PHPMailer;
use Utopia\Messaging\Adapter\Email as EmailAdapter;
use Utopia\Messaging\Messages\Email as EmailMessage;
use Utopia\Messaging\Response;

class SMTP extends EmailAdapter
{
    protected const NAME = 'SMTP';

    /**
     * @param string $host SMTP hosts. Either a single hostname or multiple semicolon-delimited hostnames. You can also specify a different port for each host by using this format: [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com"). You can also specify encryption type, for example: (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465"). Hosts will be tried in order.
     * @param int $port The default SMTP server port.
     * @param string $username Authentication username.
     * @param string $password Authentication password.
     * @param string $smtpSecure SMTP Secure prefix. Can be '', 'ssl' or 'tls'
     * @param bool $smtpAutoTLS Enable/disable SMTP AutoTLS feature. Defaults to false.
     * @param string $xMailer The value to use for the X-Mailer header.
     */
    public function __construct(
        private string $host,
        private int $port = 25,
        private string $username = '',
        private string $password = '',
        private string $smtpSecure = '',
        private bool $smtpAutoTLS = false,
        private string $xMailer = ''
    ) {
        if (!\in_array($this->smtpSecure, ['', 'ssl', 'tls'])) {
            throw new \InvalidArgumentException('Invalid SMTP secure prefix. Must be "", "ssl" or "tls"');
        }
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
     */
    protected function process(EmailMessage $message): array
    {
        $response = new Response($this->getType());
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->XMailer = $this->xMailer;
        $mail->Host = $this->host;
        $mail->Port = $this->port;
        $mail->SMTPAuth = !empty($this->username) && !empty($this->password);
        $mail->Username = $this->username;
        $mail->Password = $this->password;
        $mail->SMTPSecure = $this->smtpSecure;
        $mail->SMTPAutoTLS = $this->smtpAutoTLS;
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $message->getSubject();
        $mail->Body = $message->getContent();
        $mail->setFrom($message->getFromEmail(), $message->getFromName());
        $mail->addReplyTo($message->getReplyToEmail(), $message->getReplyToName());
        $mail->isHTML($message->isHtml());

        // Strip tags misses style tags, so we use regex to remove them
        $mail->AltBody = \preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $mail->Body);
        $mail->AltBody = \strip_tags($mail->AltBody);
        $mail->AltBody = \trim($mail->AltBody);

        foreach ($message->getTo() as $to) {
            $mail->addAddress($to);
        }

        $sent = $mail->send();

        if ($sent) {
            $response->setDeliveredTo(\count($message->getTo()));
        }

        foreach ($message->getTo() as $to) {
            $response->addResultForRecipient($to, $sent ? '' : $mail->ErrorInfo);
        }

        return $response->toArray();
    }
}
