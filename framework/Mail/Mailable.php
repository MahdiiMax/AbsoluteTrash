<?php

declare(strict_types=1);

namespace Trash\Mail;

abstract class Mailable
{
    private ?Message $message = null;

    abstract public function build(): void;

    public function to(string $address, ?string $value = null): static
    {
        $this->ensureMessage();
        $this->message = $this->message->to($address, $value);
        return $this;
    }

    public function cc(string $address, ?string $value = null): static
    {
        $this->ensureMessage();
        $this->message = $this->message->cc($address, $value);
        return $this;
    }

    public function bcc(string $address, ?string $value = null): static
    {
        $this->ensureMessage();
        $this->message = $this->message->bcc($address, $value);
        return $this;
    }

    public function subject(string $subject): static
    {
        $this->ensureMessage();
        $this->message = $this->message->subject($subject);
        return $this;
    }

    public function view(string $view, array $data = []): static
    {
        $this->ensureMessage();
        $html = view($view, $data)->render();
        $this->message = $this->message->html($html);
        return $this;
    }

    public function text(string $body): static
    {
        $this->ensureMessage();
        $this->message = $this->message->text($body);
        return $this;
    }

    public function html(string $body): static
    {
        $this->ensureMessage();
        $this->message = $this->message->html($body);
        return $this;
    }

    public function attach(string $path, ?string $name = null): static
    {
        $this->ensureMessage();
        $this->message = $this->message->attach($path, $name);
        return $this;
    }

    public function getMessage(): Message
    {
        if ($this->message === null) {
            $this->build();
        }
        return $this->message ?? new Message();
    }

    private function ensureMessage(): void
    {
        if ($this->message === null) {
            $this->message = new Message();
        }
    }
}
