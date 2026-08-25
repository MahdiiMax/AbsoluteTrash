<?php

declare(strict_types=1);

namespace Trash\Auth\Guards;

use App\Models\User;
use Trash\Session\Store;

class SessionGuard
{
    private ?User $user = null;

    public function login(User $user): void
    {
        $this->user = $user;
        $this->session()->set($this->key(), $user->id);
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session()->forget($this->key());
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }
        $id = $this->session()->get($this->key());
        if ($id === null) {
            return null;
        }
        $this->user = User::find((int) $id);
        return $this->user;
    }

    public function id(): ?int
    {
        $id = $this->session()->get($this->key());
        return $id !== null ? (int) $id : null;
    }

    private function session(): Store
    {
        return app(Store::class);
    }

    private function key(): string
    {
        return config('auth.session.key', '_auth_user_id');
    }
}
