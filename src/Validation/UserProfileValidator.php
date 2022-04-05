<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class UserProfileValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'full_name' => $this->getNameRules(),
            'email' => $this->getEmailRules(),
            'password' => $this->getPasswordRules(),
            'password_confirm' => $this->getPasswordRules(),
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
            'full_name',
            'email',
            'password',
            'password_confirm',
        ];
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

    /**
     * Возвращает правила валидации для кода подтверждения номера телефона
     *
     * @return array
     */
    private function getNameRules(): array
    {
        return [
            new Assert\Regex([
                'pattern' => '/^[\w\s\-]+$/iu',
                'message' => 'ФИО содержит недопустимые символы',
            ]),
        ];
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
            new Assert\Regex([
                'pattern' => '/\S{5,}/',
                'message' => 'Пароль должен содержать не менее 5 символов',
            ])
        ];
    }
}
