<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class FeedbackDataValidator extends AbstractValidator
{
    /**
     * @return array
     */
    protected function getConstraints(): array
    {
        return [
            'message' => $this->getMessageRules(),
            'email' => $this->getEmailRules(),
            'name' => $this->getNameRules(),
        ];
    }

    /**
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
            $this->getNotBlank(),
            new Assert\Email([
                'message' => 'Введите e-mail в формате example@example.com',
            ]),
        ];
    }

    /**
     * @return array
     */
    private function getNameRules(): array
    {
        return [
            $this->getNotBlank(),
            new Assert\Regex([
                'pattern' => '/<[a-z][\s\S]*>/i',
                'message' => 'Поле содержит недопустимые символы',
                'match' => false,
            ]),
        ];
    }

    /**
     * @return array
     */
    private function getMessageRules(): array
    {
        return [
            $this->getNotBlank(),
            new Assert\Regex([
                'pattern' => '/<[a-z][\s\S]*>/i',
                'message' => 'Поле содержит недопустимые символы',
                'match' => false,
            ]),
        ];
    }
}
