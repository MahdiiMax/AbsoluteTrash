<?php

declare(strict_types=1);

namespace Trash\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string|array $methods,
        public string $path,
        public ?string $name = null,
        public array $middleware = []
    ) {}
}
