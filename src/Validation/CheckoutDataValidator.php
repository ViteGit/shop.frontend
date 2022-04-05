<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutDataValidator extends AbstractValidator
{
    /**
     * Возвращает список полей с правилами валидации
     *
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'fio' => $this->getFioRules(),
            'email' => $this->getEmailRules(),
            'phone' => $this->getPhoneRules(),
            'city' => [$this->getNotBlank()],
            'country' => [$this->getNotBlank()],
            'address' => [$this->getNotBlank()],
            'postcode' => [$this->getNotBlank()],
            'shipmentMethod' => [$this->getNotBlank()],
            'paymentMethod' => [$this->getNotBlank()],
            'privacy_policy' => [$this->getNotBlank()],
            'pickpoint_id' => [],
            'notes' => [],
        ];
    }

    /**
     * Возвращает список необязательных полей
     *
     * @return array
     */
    protected function getOptionalFields(): array
    {
        return ['postcode', 'address', 'pickpoint_id'];
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
}
