<?php

declare(strict_types=1);

namespace Trash\Mail;

use Trash\Mail\Transport\SmtpTransport;

class Mailer
{
    public function __construct(
        private SmtpTransport $transport
    ) {}

    public function send(Mailable $mailable): void
    {
        $message = $mailable->getMessage();
        $this->sendRaw($message);
    }

    public function raw(string $to, string $subject, string $body): void
    {
        $message = (new Message())
            ->to($to)
            ->subject($subject)
            ->text($body);
        $this->sendRaw($message);
    }

    public function html(string $to, string $subject, string $body): void
    {
        $message = (new Message())
            ->to($to)
            ->subject($subject)
            ->html($body);
        $this->sendRaw($message);
    }

    private function sendRaw(Message $message): void
    {
        $from = config('mail.from.address', 'hello@example.com');
        $recipients = $message->getRecipients();
        $rawMessage = $message->buildRaw();
        $this->transport->send($from, $recipients, $rawMessage);
    }
}
