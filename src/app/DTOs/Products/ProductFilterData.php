<?php

namespace App\DTOs\Products;

final readonly class ProductFilterData
{
    public function __construct(
        public int $userId,
        public ?string $name = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            name: $data['name'] ?? null,
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            perPage: (int) ($data['per_page'] ?? 15),
            page: (int) ($data['page'] ?? 1),
        );
    }

    /**
     * Parameters used to build the listing cache key — must uniquely identify this filter
     * combination, including the owner, so cached pages never leak across users.
     *
     * @return array<string, mixed>
     */
    public function toCacheParams(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }
}
