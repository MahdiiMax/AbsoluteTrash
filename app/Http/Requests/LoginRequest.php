<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Override;
use Trash\Http\FormRequest;

class LoginRequest extends FormRequest
{
    #[Override]
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required'
        ];
    }
}
