<?php

namespace App\VO;

use InvalidArgumentException;

class ColorSelect
{
    private const RED = 'rood';
    private const VIOLET = 'paars';
    private const ORANGE = 'oranje';
    private const YELLOW = 'geel';
    private const GREEN = 'groen';
    private const TRANSPARENT = 'transparant';
    private const DEEP_BLUE = 'blauw';
    private const ROSE = 'roze';
    private const WHITE = 'wit';
    private const BLACK = 'zwart';
    private const GOLD = 'goud';
    private const SILVER = 'zilver';
    private const BLUE = 'turquoise';
    private const BROWN = 'bruin';
    private const BEAGE = 'beige';
    private const GRAY = 'grijs';
    private const CREAM = 'creme';

    private const VALID_VALUES = [
        self::RED,
        self::VIOLET,
        self::ORANGE,
        self::YELLOW,
        self::GREEN,
        self::TRANSPARENT,
        self::DEEP_BLUE,
        self::WHITE,
        self::BLACK,
        self::GOLD,
        self::SILVER,
        self::BLUE,
        self::BROWN,
        self::BEAGE,
        self::GRAY,
        self::CREAM,
        self::ROSE,
    ];

    public const SELECT = [
        self::RED => 'красный',
        self::VIOLET => 'фиолетовый',
        self::ORANGE => 'оранжевый',
        self::YELLOW => 'желтый',
        self::GREEN => 'зеленый',
        self::TRANSPARENT => 'прозрачный',
        self::DEEP_BLUE => 'синий',
        self::WHITE => 'розовый',
        self::BLACK => 'черный',
        self::GOLD => 'золотой',
        self::SILVER => 'серебро',
        self::BLUE => 'голубой',
        self::BROWN => 'коричневый',
        self::BEAGE => 'бежевый',
        self::GRAY => 'серый',
        self::CREAM => 'кремовый',
        self::ROSE => 'розовый',
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