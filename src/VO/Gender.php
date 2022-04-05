<?php

namespace App\VO;

use InvalidArgumentException;

class Gender
{
    /**
     * Мужской пол
     */
    public const MALE = 'male';

    /**
     * Женский пол
     */
    public const FEMALE = 'female';

    /**
     * Допустимые значения пола
     */
    public const VALID_VALUES = [
        self::MALE,
        self::FEMALE,
    ];

    /**
     * Перевод статусов на человеческий язык
     */
    public const VIEW_VALUES = [
        self::MALE => 'Мужской',
        self::FEMALE => 'Женский',
    ];

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_VALUES)) {
            throw new InvalidArgumentException('Неизвестный Гендр');
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

    /**
     * Возвращает перевод пола на человеческий язык
     *
     * @return string
     */
    public function getName(): string
    {
        return self::VIEW_VALUES[$this->value];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue();
    }
}
