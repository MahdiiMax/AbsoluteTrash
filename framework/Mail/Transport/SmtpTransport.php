<?php

declare(strict_types=1);

namespace Trash\Mail\Transport;

use RuntimeException;

class SmtpTransport
{
    private $socket = null;

    public function __construct(
        private string $host,
        private int $port = 587,
        private ?string $username = null,
        private ?string $password = null,
        private string $encryption = 'tls'
    ) {}

    public function send(string $from, array $recipients, string $rawMessage): void
    {
        $this->connect();
        $this->expect(220);
        $this->sendCommand("EHLO " . gethostname());
        $this->expect(250);
        if ($this->encryption === 'tls') {
            $this->sendCommand("STARTTLS");
            $this->expect(220);
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            $this->sendCommand("EHLO " . gethostname());
            $this->expect(250);
        }
        if ($this->username !== null && $this->password !== null) {
            $this->sendCommand("AUTH LOGIN");
            $this->expect(334);
            $this->sendCommand(base64_encode($this->username));
            $this->expect(334);
            $this->sendCommand(base64_encode($this->password));
            $this->expect(235);
        }
        $this->sendCommand("MAIL FROM:<{$from}>");
        $this->expect(250);
        foreach ($recipients as $recipient) {
            $this->sendCommand("RCPT TO:<{$recipient}>");
            $this->expect(250);
        }
        $this->sendCommand("DATA");
        $this->expect(354);
        $this->sendCommand($rawMessage);
        $this->sendCommand(".");
        $this->expect(250);
        $this->sendCommand("QUIT");
        $this->expect(221);
        $this->disconnect();
    }

    private function connect(): void
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if ($this->socket === false) {
            throw new RuntimeException("Could not connect to SMTP host [{$this->host}:{$this->port}]: {$errstr} ({$errno})");
        }
        stream_set_timeout($this->socket, 30);
    }

    private function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->socket = null;
    }

    private function sendCommand(string $command): void
    {
        fwrite($this->socket, $command . "\r\n");
    }

    private function expect(int $code): void
    {
        $response = fgets($this->socket, 4096);
        if ($response === false || (int) $response !== $code) {
            $this->disconnect();
            throw new RuntimeException("SMTP error: expected {$code}, got [{$response}]");
        }
    }
}
