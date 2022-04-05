<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePasswordDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'password' => $this->getPasswordRules(),
            'confirm_password' => $this->getPasswordRules(),
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
     * TODO: тут будет добавлена валидация пароля
     *
     * Возвращает правила валидации для пароля
     *
     * @return array
     */
    private function getPasswordRules(): array
    {
        return [
            new Assert\NotBlank([
                'message' => 'Поле обязательно к заполнению',
            ]),
            new Assert\Regex([
                'pattern' => '/\S{5,}/',
                'message' => 'Пароль должен содержать не менее 5 символов',
            ])
        ];
    }
}
