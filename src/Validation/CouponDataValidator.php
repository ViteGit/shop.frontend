<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class CouponDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'coupon_code' => [$this->getNotBlank()],
        ];
    }

    /**
     * Возвращает список необязательных полей
     *
     * @return array
     */
    protected function getOptionalFields(): array
    {
        return [];
    }
}
