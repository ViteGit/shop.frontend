<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class ReviewDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'nickname' => $this->getNameRules(),
            'email' => $this->getEmailRules(),
            'rating' => $this->getIdRules(),
            'comment' => $this->getNotBlank(),
            'productId' => $this->getIdRules(),
            'subject' => $this->getNotBlank(),
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
            'nickname',
            'subject'
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
}
