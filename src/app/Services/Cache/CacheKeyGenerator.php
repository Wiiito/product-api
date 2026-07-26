<?php

namespace App\Services\Cache;

use App\Interfaces\Cache\CacheKeyGeneratorInterface;

class CacheKeyGenerator implements CacheKeyGeneratorInterface
{
    public function generate(string $prefix, array $params = []): string
    {
        ksort($params);

        return sprintf('%s:%s', $prefix, md5(json_encode($params)));
    }
}
