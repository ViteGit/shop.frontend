<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class RestorePasswordValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'email' => $this->getEmailRules(),
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

    /**
     * @return array
     */
    private function getEmailRules(): array
    {
        return [
            new Assert\Email([
                'message' => 'Введите e-mail в формате example@example.com',
            ]),
        ];
    }
}
