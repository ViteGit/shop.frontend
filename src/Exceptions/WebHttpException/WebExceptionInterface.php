<?php

namespace App\Exceptions\WebHttpException;

interface WebExceptionInterface
{
    /**
     * @return array
     */
    public function getTemplateVars(): array;

    /**
     * @return string | null
     */
    public function getTemplate(): ?string;

    /**
     * @return array | string[]
     */
    public function getErrors(): array;
}
