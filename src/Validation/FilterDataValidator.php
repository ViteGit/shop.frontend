<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class FilterDataValidator extends AbstractValidator
{
    /**
     * @return array
     */
    protected function getConstraints(): array
    {
        return [

        ];
    }

    /**
     * @return array
     */
    protected function getOptionalFields(): array
    {
        return [];
    }
}
