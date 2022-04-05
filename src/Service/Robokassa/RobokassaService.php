<?php

namespace App\Service\Robokassa;

use App\Entity\Payment;
use App\Entity\RobokassaPayment;
use App\Exceptions\Robokassa\RobokassaException;
use App\VO\PaymentStatus;
use App\VO\Robokassa\ResponseStatus;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class RobokassaService
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var bool
     */
    private $testMode;

    /**
     * @var string
     */
    private $password1;

    /**
     * @var string
     */
    private $password2;

    /**
     * @var string
     */
    private $apiUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    /**
     * @var string
     */
    private $xmlApiUrl = 'https://auth.robokassa.ru/Merchant/WebService/Service.asmx';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     * @param string $robokassaMerchantLogin
     * @param string $password1
     * @param string $password2
     * @param bool $testMode
     */
    public function __construct(
        EntityManagerInterface $em,
        string $robokassaMerchantLogin,
        string $password1,
        string  $password2,
        bool $testMode
    ) {
        $this->em = $em;
        $this->password1 = $password1;
        $this->password2 = $password2;
        $this->login = $robokassaMerchantLogin;
        $this->testMode = $testMode;
        $this->client = new Client([]);
    }

    public function getPaymentUrl(int $invId, int $outSum, string $description)
    {
        $parameters = [
            'MerchantLogin' => $this->login,
            'OutSum' => $outSum,
            'InvId' => $invId,
            'Desc' => $description,
            'IncCurrLabel' => '',
            'SignatureValue' => $this->sign($this->login, $outSum, $invId)
        ];

        if ($this->testMode) {
            $parameters['IsTest'] = 1;
        }

        return $this->apiUrl . '?' . http_build_query($parameters);
    }

    /**
     * @param RobokassaPayment $payment
     *
     * @return Payment
     *
     * @throws RobokassaException
     * @throws Exception
     */
    public function approveAndDeposit(RobokassaPayment $payment): Payment
    {
        $responseStatus = $this->getOpState($payment->getInvId());

        switch ($responseStatus) {
            case ResponseStatus::STATUS_COMPLETED:
                $paymentStatus = PaymentStatus::COMPLETED;
                $payment->updatePaymentDate();

                break;
            case ResponseStatus::STATUS_PENDING:

                throw new RobokassaException([],"Неожиданный статус платежа: $responseStatus", Response::HTTP_INTERNAL_SERVER_ERROR);
            case ResponseStatus::STATUS_CANCELLED:
                $paymentStatus = PaymentStatus::CANCELLED;
                $payment->updateCancelDate();

                break;
            case ResponseStatus::STATUS_REFUND:
                $paymentStatus = PaymentStatus::REFUNDED;

                throw new RobokassaException([],"Механизм возврата денежный средств не предусмотрен системой: Статус ответа $responseStatus");
            default:
                throw new RobokassaException([],"Неизвестный статус платежа: $responseStatus");
        }

        $payment->updateStatus(new PaymentStatus($paymentStatus));
        $payment->updateResponseStatus(new ResponseStatus($responseStatus));

        $this->em->flush();

        return $payment;
    }

    /**
     * @param $login
     * @param $out_sum
     * @param $inv_id
     *
     * @return string
     */
    public function sign($login, $out_sum, $inv_id)
    {
        return md5($login . ':' . $out_sum . ':' . $inv_id . ':' . $this->password1);
    }

    /**
     * @param $login
     * @param $inv_id
     * @return string
     */
    public function signXML($login, $inv_id)
    {
        return md5($login . ':' . $inv_id . ':' . $this->password2);
    }

    /**
     * @param $sign
     * @param $outSum
     * @param $invId
     *
     * @return bool
     */
    public function validateResult($sign, $outSum, $invId): bool
    {
        $crc = md5("$outSum:$invId:$this->password2");

        return strtoupper($sign) === strtoupper($crc);
    }

    /**
     * @param $sign
     * @param $outSum
     * @param $invId
     * @param array $params
     *
     * @return bool
     */
    public function validateSuccess($sign, $outSum, $invId, array $params = []): bool
    {
        $crc = md5("$outSum:$invId:$this->password1");

        return strtoupper($sign) === strtoupper($crc);
    }

    /**
     * @param string $uri
     * @param array  $parameters
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function post($uri, array $parameters = [])
    {
        return $this->client->post($uri, ['form_params' => $parameters]);
    }

    /**
     * @param int $invId
     *
     * @return int
     *
     * @throws RobokassaException
     */
    public function getOpState(int $invId)
    {
        $params = [
            'MerchantLogin' => $this->login,
            'InvoiceID' => $invId,
            'Signature' => $this->signXML($this->login, $invId),
        ];

        if ($this->testMode) {
            $params['IsTest'] = 1;
        }

        $response = $this->post("$this->xmlApiUrl/OpState", $params);

        $xml = new SimpleXMLElement((string)$response->getBody());

        $result = $xml->Result;
        $resultCode = (int) $result->Code;

        if ($resultCode !== 0) {
            throw new RobokassaException([], (string) "$result->Description Код ошибки: $resultCode", $resultCode);
        }

        return (int) $xml->State->Code;
    }
}
