<?php

namespace App\VO;

use InvalidArgumentException;

class SizeSelect
{
    private const SIZE_XS = 'XS';
    private const SIZE_S = 'S';
    private const SIZE_M = 'M';
    private const SIZE_L = 'L';
    private const SIZE_XL = 'XL';
    private const SIZE_XXL = 'XXL';
    private const SIZE_XXXL = 'XXXL';
    private const SIZE_2XL = '2XL';
    private const SIZE_3XL = '3XL';
    private const SIZE_4XL = '4XL';

    public const VALID_VALUES = [
        self::SIZE_XS,
        self::SIZE_S,
        self::SIZE_M,
        self::SIZE_L,
        self::SIZE_XL,
        self::SIZE_XXL,
        self::SIZE_XXXL,
        self::SIZE_2XL,
        self::SIZE_3XL,
        self::SIZE_4XL,
    ];

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_VALUES)) {
            throw new InvalidArgumentException("Неизвестный параметр $value");
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
