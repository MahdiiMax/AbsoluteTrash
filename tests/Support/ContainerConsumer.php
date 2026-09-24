<?php

declare(strict_types=1);

namespace Tests\Support;

final class ContainerConsumer
{
    public function __construct(private ContainerService $service)
    {
    }

    public function service(): ContainerService
    {
        return $this->service;
    }
}