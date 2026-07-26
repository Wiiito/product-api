<?php

namespace App\Interfaces\Cache;

interface CacheKeyGeneratorInterface
{
    /**
     * Build a deterministic cache key from a prefix and a set of parameters.
     *
     * @param  array<string, mixed>  $params
     */
    public function generate(string $prefix, array $params = []): string;
}
