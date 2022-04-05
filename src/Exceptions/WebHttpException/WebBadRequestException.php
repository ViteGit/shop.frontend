<?php

namespace App\Exceptions\WebHttpException;

use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebBadRequestException extends BadRequestHttpException implements WebExceptionInterface
{
    /**
     * @var array
     */
    private $templateVars;

    /**
     * @var string | null
     */
    private $template;

    /**
     * @var array | string[]
     */
    private $errors;

    /**
     * @param string | null    $template
     * @param array            $errors
     * @param array            $templateVars
     * @param string           $message
     * @param int              $code
     * @param Exception | null $previous
     */
    public function __construct(
        array $errors,
        ?string $template = null,
        array $templateVars = [],
        string $message = '',
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $previous, $code);

        $this->template = $template;
        $this->errors = $errors;
        $this->templateVars = $templateVars;
    }

    /**
     * @return array
     */
    public function getTemplateVars(): array
    {
        return $this->templateVars;
    }

    /**
     * @return string | null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @return array | string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
