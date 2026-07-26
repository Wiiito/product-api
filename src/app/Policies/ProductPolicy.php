<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductPolicy
{
    public function view(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    private function owns(User $user, Product $product): bool
    {
        if ($user->id !== $product->user_id) {
            throw new ModelNotFoundException;
        }

        return true;
    }
}
