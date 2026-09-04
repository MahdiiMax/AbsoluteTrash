<?php

declare(strict_types=1);

namespace Trash\Http;

use Psr\Http\Message\ServerRequestInterface;
use Trash\Validation\Validator;

abstract class FormRequest
{
    private array $validated = [];
    private array $errors = [];

    public function __construct(ServerRequestInterface $request)
    {
        $input = array_merge(
            $request->getQueryParams(),
            $request->getParsedBody() ?? []
        );
        if (!$this->authorize()) {
            abort(403, 'Forbidden');
        }
        $validator = new Validator($input, $this->rules());
        if ($validator->fails()) {
            $this->errors = $validator->errors();
            if ($this->wantsJson($request)) {
                abort(422, json_encode($this->errors));
            }
            throw new ValidationException($validator->errors());
        }
        $this->validated = $validator->validated();
    }

    abstract public function rules(): array;

    public function authorize(): bool
    {
        return true;
    }

    public function validated(): array
    {
        return $this->validated;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->validated;
        }
        return $this->validated[$key] ?? $default;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function wantsJson(ServerRequestInterface $request): bool
    {
        return str_contains($request->getHeaderLine('Accept'), 'application/json')
            || $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }
}
