<?php

namespace App\DTO;

class CartData
{
    /**
     * @var int | null
     */
    private $variantId;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param int $variantId
     * @param int $quantity
     */
    public function __construct(int $variantId, int $quantity)
    {
        $this->variantId = $variantId;
        $this->quantity = $quantity;
    }

    /**
     * @return int|null
     */
    public function getVariantId(): ?int
    {
        return $this->variantId;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
