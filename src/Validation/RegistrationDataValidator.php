<?php

namespace App\Validation;

use App\VO\Gender;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'login' => $this->getLoginRules(),
            'fio' => $this->getFioRules(),
            'email' => $this->getEmailRules(),
            'gender' => $this->getGenderRules(),
            'phone' => $this->getPhoneRules(),
            'city' => [$this->getNotBlank()],
            'address' => [$this->getNotBlank()],
            'postcode' => [$this->getNotBlank()],
            'password' => $this->getPasswordRules(),
            'repeat_password' => $this->getPasswordRules(),
            'agree' => $this->getNotBlank(),
        ];
    }

    /**
     * Возвращает список необязательных полей
     *
     * @return array
     */
    protected function getOptionalFields(): array
    {
        return ['postcode', 'address', 'city', 'gender'];
    }


    /**
     * Возвращает правила валидации для кода подтверждения номера телефона
     *
     * @return array
     */
    private function getFioRules(): array
    {
        return [
            new Assert\Regex([
                'pattern' => '/^[\w\s\-]+$/iu',
                'message' => 'ФИО содержит недопустимые символы',
            ]),
        ];
    }

    /**
     * @return array
     */
    private function getLoginRules(): array
    {
        return [
            $this->getNotBlank(),
        ];
    }

    private function getGenderRules(): array
    {
        return [
            new Assert\Choice(Gender::VALID_VALUES)
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
     * Возвращает правила валидации для номера телефона
     *
     * @return array
     */
    private function getPhoneRules(): array
    {
        return [
            new Assert\Regex([
                'pattern' => '/^\+\d\s\(\d{3}\)\s\d{3}-\d{4}$/',
                'message' => 'Номер телефона должен состоять из 11 цифр',
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
