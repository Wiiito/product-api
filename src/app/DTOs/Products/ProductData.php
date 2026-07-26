<?php

namespace App\DTOs\Products;

final readonly class ProductData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public float $price,
        public int $quantity,
        public ?int $userId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?int $userId = null): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            price: (float) $data['price'],
            quantity: (int) ($data['quantity'] ?? 0),
            userId: $userId,
        );
    }

    /**
     * Owner is only included when known (creation) — omitted on update so the
     * product's existing owner is never touched by the update payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];

        if ($this->userId !== null) {
            $data['user_id'] = $this->userId;
        }

        return $data;
    }
}
