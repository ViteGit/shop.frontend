<?php

namespace App\VO;

use InvalidArgumentException;

class DiameterSelect
{
    private const ANY = '0-100';
    private const SMALL_DIAMETER = '1-3';
    private const MEDIUM_DIAMETER = '4-6';
    private const BIG_DIAMETER = '7-10';
    private const EXTRA_DIAMETER = '11-100';

    private const VALID_VALUES = [
        self::ANY,
        self::SMALL_DIAMETER,
        self::MEDIUM_DIAMETER,
        self::BIG_DIAMETER,
        self::EXTRA_DIAMETER,
    ];

    public const SELECT = [
        self::ANY => 'любой',
        self::SMALL_DIAMETER => '1 - 3 см',
        self::MEDIUM_DIAMETER => '4 - 6 см',
        self::BIG_DIAMETER => '7 - 10 см',
        self::EXTRA_DIAMETER => '11+ см',
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
