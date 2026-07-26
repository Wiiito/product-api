<?php

namespace Tests\Unit\Cache;

use App\Services\Cache\CacheKeyGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CacheKeyGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_the_same_key_regardless_of_param_order(): void
    {
        $generator = new CacheKeyGenerator;

        $keyA = $generator->generate('products.index', ['name' => 'mouse', 'min_price' => 50]);
        $keyB = $generator->generate('products.index', ['min_price' => 50, 'name' => 'mouse']);

        $this->assertSame($keyA, $keyB);
    }

    #[Test]
    public function it_generates_different_keys_for_different_params(): void
    {
        $generator = new CacheKeyGenerator;

        $keyA = $generator->generate('products.index', ['name' => 'mouse']);
        $keyB = $generator->generate('products.index', ['name' => 'teclado']);

        $this->assertNotSame($keyA, $keyB);
    }

    #[Test]
    public function it_prefixes_the_generated_key(): void
    {
        $generator = new CacheKeyGenerator;

        $key = $generator->generate('products.index', ['name' => 'mouse']);

        $this->assertStringStartsWith('products.index:', $key);
    }
}
