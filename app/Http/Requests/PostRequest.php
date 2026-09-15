<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Override;
use Trash\Http\FormRequest;

class PostRequest extends FormRequest
{
    #[Override]
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ];
    }
}
