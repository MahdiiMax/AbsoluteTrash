<?php

declare(strict_types=1);

namespace Trash\Mail;

use Override;
use Trash\Foundation\ServiceProvider;
use Trash\Mail\Transport\SmtpTransport;

class MailServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(SmtpTransport::class, fn() => new SmtpTransport(
            config('mail.host', '127.0.0.1'),
            (int) config('mail.port', 587),
            config('mail.username'),
            config('mail.password'),
            config('mail.encryption', 'tls')
        ));
        $this->app->singleton(Mailer::class, fn() => new Mailer(
            $this->app->make(SmtpTransport::class)
        ));
    }
}
