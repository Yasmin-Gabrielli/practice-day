<?php

declare(strict_types=1);

namespace App\Middleware;

interface Middleware
{
    public function handle(): void;
}
