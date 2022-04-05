<?php

namespace App\VO;

use InvalidArgumentException;

class Sort
{
    public const NEW = 'new';
    public const BESTSELLER = 'bestseller';
    public const RECOMMENDED = 'recommended';
    public const DISCOUNT = 'discount';
    public const PRICE_ASC = 'price_asc';
    public const PRICE_DESC = 'price_desc';

    private const VALID_VALUES = [
        self::NEW,
        self::BESTSELLER,
        self::RECOMMENDED,
        self::DISCOUNT,
        self::PRICE_ASC,
        self::PRICE_DESC,
    ];

    public const SELECT = [
        self::NEW => 'новинки',
        self::BESTSELLER => 'популярные',
        self::RECOMMENDED => 'рекомендованные',
        self::DISCOUNT => 'со скидкой',
        self::PRICE_ASC => 'цена по возрастанию',
        self::PRICE_DESC => 'цена по убыванию',
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
