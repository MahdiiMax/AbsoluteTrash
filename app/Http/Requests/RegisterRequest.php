<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Override;
use Trash\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    #[Override]
    public function rules(): array
    {
        return [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
