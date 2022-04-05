<?php

namespace App\VO;

use InvalidArgumentException;

class LengthSelect
{
    private const ANY = '0-100';
    private const EXTRA_SMALL_LENGTH = '10-15';
    private const SMALL_LENGTH = '16-20';
    private const MEDIUM_LENGTH = '21-25';
    private const BIG_LENGTH = '26-30';
    private const EXTRA_LENGTH = '31-100';

    private const VALID_VALUES = [
        self::ANY,
        self::EXTRA_SMALL_LENGTH,
        self::SMALL_LENGTH,
        self::MEDIUM_LENGTH,
        self::BIG_LENGTH,
        self::EXTRA_LENGTH,
    ];

    public const SELECT = [
        self::ANY => 'любой',
        self::EXTRA_SMALL_LENGTH => '10-15 см',
        self::SMALL_LENGTH => '16-20 см',
        self::MEDIUM_LENGTH => '21-25 см',
        self::BIG_LENGTH => '26-30 см',
        self::EXTRA_LENGTH => '30+ см',
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
