<?php

namespace App\DTO\Robokassa;

class PaymentData
{

    /**
     * @var string
     */
    private $outSum;

    /**
     * @var string
     */
    private $invId;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @param string $outSum
     * @param string $invId
     * @param string $signature
     * @param array $params
     */
    public function __construct(string $outSum, string $invId, string $signature, array $params = [])
    {
        $this->invId = $invId;
        $this->outSum = $outSum;
        $this->signature = $signature;
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getInvId(): string
    {
        return $this->invId;
    }

    /**
     * @return string
     */
    public function getOutSum(): string
    {
        return $this->outSum;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }
}
