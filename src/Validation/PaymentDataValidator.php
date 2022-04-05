<?php

namespace App\Validation;

use App\VO\Gender;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'OutSum' => [$this->getNotBlank()],
            'InvId' => [$this->getNotBlank()],
            'SignatureValue' => [$this->getNotBlank()],
            'Shp_item' => [$this->getNotBlank()],
            'IsTest' => [$this->getNotBlank()],
            'Culture' => [$this->getNotBlank()],
        ];
    }

    /**
     * Возвращает список необязательных полей
     *
     * @return array
     */
    protected function getOptionalFields(): array
    {
        return [
            'Shp_item',
            'IsTest',
            'Culture',
        ];
    }
}
