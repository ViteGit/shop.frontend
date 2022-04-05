<?php

namespace App\VO\PickPoint;

class PickPointRate
{
    private const COEFFICIENT = 1.25;

    private const XS = [
        'size' => '10 * 36 * 60',
        'price' => 159.63,
        'volume_weight' => (10 * 36 * 60) / 5000,
    ];

    private const S = [
        'size' => '15 * 36 * 60',
        'price' => 189, 04,
        'volume_weight' => (15 * 36 * 60) / 5000,
    ];

    private const M = [
        'size' => '20 * 36 * 60',
        'price' => 210, 04,
        'volume_weight' => (20 * 36 * 60) / 5000,
    ];

    private const L = [
        'size' => '36 * 36 * 60',
        'price' => 235, 24,
        'volume_weight' => (36 * 36 * 60) / 5000,
    ];

    private const XL = [
        'size' => '36 * 60 * 60',
        'price' => 269, 90,
        'volume_weight' => (36 * 60 * 60) / 5000,
    ];

    private const XXL = [
        'size' => '60 * 60 * 60',
        'price' => 348, 66,
        'volume_weight' => (60 * 60 * 60) / 5000,
    ];

    private const ZONES_PRICE = [
        'Москва' => 0,
        0 => 9.24,
        1 => 13.52,
        2 => 22.53,
        3 => 39.51,
        4 => 54.06,
        5 => 70.58,
        6 => 70.58,
        7 => 70.58,
    ];

    private $value;

    public function __construct(int $zone)
    {
        if ($zone == -1) {
            $zone = 'Москва';
        }

        if (!array_key_exists($zone, self::ZONES_PRICE)) {
            throw new \InvalidArgumentException("Неизвестная зона = $zone");
        }

        $this->value = $zone;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return self::XS['price'] + (self::ZONES_PRICE[$this->value] * self::COEFFICIENT);
    }
}
