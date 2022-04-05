<?php

namespace App\Exceptions\WebHttpException;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebNotFoundException extends NotFoundHttpException implements WebExceptionInterface
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
     * @param array            $errors
     * @param string | null    $template
     * @param array            $templateVars
     * @param int              $code
     * @param string           $message
     * @param Exception | null $previous
     */
    public function __construct(
        array $errors,
        ?string $template = null,
        array $templateVars = [],
        int $code = 0,
        string $message = '',
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
