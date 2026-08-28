<?php

declare(strict_types=1);

namespace Trash\Mail;

class Message
{
    private array $to = [], $cc = [], $bcc = [], $attachments = [], $headers = [];
    private string $subject = '', $textBody = '', $htmlBody = '';

    public function to(string $address, ?string $name = null): static
    {
        $clone = clone $this;
        $clone->to[] = $name !== null ? [$address, $name] : [$address];
        return $clone;
    }

    public function cc(string $address, ?string $name = null): static
    {
        $clone = clone $this;
        $clone->cc[] = $name !== null ? [$address, $name] : [$address];
        return $clone;
    }

    public function bcc(string $address, ?string $name = null): static
    {
        $clone = clone $this;
        $clone->bcc[] = $name !== null ? [$address, $name] : [$address];
        return $clone;
    }

    public function subject(string $subject): static
    {
        $clone = clone $this;
        $clone->subject = $subject;
        return $clone;
    }

    public function text(string $body): static
    {
        $clone = clone $this;
        $clone->textBody = $body;
        return $clone;
    }

    public function html(string $body): static
    {
        $clone = clone $this;
        $clone->htmlBody = $body;
        return $clone;
    }

    public function attach(string $path, ?string $name = null): static
    {
        $clone = clone $this;
        $clone->attachments[] = [
            'path' => $path,
            'name' => $name ?? basename($path),
        ];
        return $clone;
    }

    public function header(string $name, string $value): static
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTextBody(): string
    {
        return $this->textBody;
    }

    public function getHtmlBody(): string
    {
        return $this->htmlBody;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRecipients(): array
    {
        $recipients = [];
        foreach (array_merge($this->to, $this->cc, $this->bcc) as $address) {
            $recipients[] = $address[0];
        }
        return $recipients;
    }

    public function buildRaw(): string
    {
        $boundary = md5(uniqid((string) time(), true));
        $lines = [];
        $lines[] = "MIME-Version: 1.0";
        $lines[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
        $lines[] = "Subject: {$this->subject}";
        foreach ($this->headers as $name => $value) {
            $lines[] = "{$name}: {$value}";
        }
        $lines[] = "";
        if ($this->htmlBody !== '' && $this->textBody !== '') {
            $lines[] = "--{$boundary}";
            $lines[] = "Content-Type: text/plain; charset=UTF-8";
            $lines[] = "";
            $lines[] = $this->textBody;
            $lines[] = "";
            $lines[] = "--{$boundary}";
            $lines[] = "Content-Type: text/html; charset=UTF-8";
            $lines[] = "";
            $lines[] = $this->htmlBody;
            $lines[] = "";
        } elseif ($this->htmlBody !== '') {
            $lines[] = "--{$boundary}";
            $lines[] = "Content-Type: text/html; charset=UTF-8";
            $lines[] = "";
            $lines[] = $this->htmlBody;
            $lines[] = "";
        } else {
            $lines[] = "--{$boundary}";
            $lines[] = "Content-Type: text/plain; charset=UTF-8";
            $lines[] = "";
            $lines[] = $this->textBody;
            $lines[] = "";
        }
        foreach ($this->attachments as $attachment) {
            $content = file_get_contents($attachment['path']);
            $encoded = chunk_split(base64_encode($content));
            $lines[] = "--{$boundary}";
            $lines[] = "Content-Type: application/octet-stream; name=\"{$attachment['name']}\"";
            $lines[] = "Content-Transfer-Encoding: base64";
            $lines[] = "Content-Disposition: attachment; filename=\"{$attachment['name']}\"";
            $lines[] = "";
            $lines[] = $encoded;
            $lines[] = "";
        }
        $lines[] = "--{$boundary}--";
        return implode("\r\n", $lines);
    }
}
