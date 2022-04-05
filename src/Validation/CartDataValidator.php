<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class CartDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'variantId' => $this->getIdRules(),
            'quantity' => $this->getIdRules(),
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
