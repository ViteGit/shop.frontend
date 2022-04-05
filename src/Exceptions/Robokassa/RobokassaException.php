<?php

namespace App\Exceptions\Robokassa;

use Exception;
use Throwable;

class RobokassaException extends Exception
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @param array            $errors
     * @param string           $message
     * @param int              $code
     * @param Throwable | null $previous
     */
    public function __construct(
        array $errors,
        string $message = "",
        int $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
