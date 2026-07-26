<?php

namespace Tests\Unit\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    private ProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ProductPolicy;
    }

    #[Test]
    public function the_owner_may_view_update_and_delete_their_product(): void
    {
        $owner = (new User)->forceFill(['id' => 1]);
        $product = new Product(['user_id' => 1]);

        $this->assertTrue($this->policy->view($owner, $product));
        $this->assertTrue($this->policy->update($owner, $product));
        $this->assertTrue($this->policy->delete($owner, $product));
    }

    #[Test]
    public function another_user_may_not_view_update_or_delete_the_product(): void
    {
        $someoneElse = new User(['id' => 2]);
        $product = new Product(['user_id' => 1]);

        $this->assertModelNotFound(fn () => $this->policy->view($someoneElse, $product));
        $this->assertModelNotFound(fn () => $this->policy->update($someoneElse, $product));
        $this->assertModelNotFound(fn () => $this->policy->delete($someoneElse, $product));
    }

    private function assertModelNotFound(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected a ModelNotFoundException to be thrown.');
        } catch (ModelNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
