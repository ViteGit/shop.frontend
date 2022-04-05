<?php

namespace App\Service;

use App\VO\DsPlatformaApi\Delivery;
use App\VO\DsPlatformaApi\OrderPaid;
use App\VO\Email;
use App\VO\PhoneNumber;
use GuzzleHttp\Client;

class DsPlatformaApi
{
    /**
     * HTTP-коды ответов
     */
    private const HTTP_OK = 200;
    private const HTTP_FORBIDDEN = 403;
    private const HTTP_GATEWAY_TIMEOUT = 504;

    private const CREATE_ORDER = '/ds_order.php';

    private const GET_ORDER_DATA = '/ds_get_order_data.php';

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $testMode;

    /**
     * @param string $dsPlatformaApi
     * @param string $apiKey
     * @param string $orderIds
     * @param string $extOrderIds
     * @param string $testMode
     */
    public function __construct(
        string $dsPlatformaApi,
        string $apiKey,
        string $testMode
    ) {
        $this->testMode = $testMode;
        $this->apiKey = $apiKey;
        $this->apiUrl = $dsPlatformaApi;
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'http_errors' => false,
//            'headers' => $this->headers,
        ]);
    }

    /**
     * @param int $aId
     * @param int $quantity
     * @param int $price
     * @param int $orderId
     * @param OrderPaid $orderPaid
     * @param int $deliveryCost
     * @param Delivery $delivery
     * @param string $fio
     * @param PhoneNumber $phone
     * @param Email $email
     * @param string $city
     * @param string $dsPickUpId
     * @return array
     */
    public function createOrder(
        int $aId,
        int $quantity,
        int $price,
        int $orderId,
        OrderPaid $orderPaid,
        int $deliveryCost,
        Delivery $delivery,
        string $fio,
        PhoneNumber $phone,
        Email $email,
        string $city,
        string $dsPickUpId
    ): array {
        $body = [
            'query' => [
                'ApiKey' => $this->apiKey,
                'order' => "$aId-$quantity-$price",
                'ExtOrderID' => $orderId,
                'ExtOrderPaid' => $orderPaid->getValue(),
                'ExtDeliveryCost' => $deliveryCost,
                'dsDelivery' => $delivery->getValue(),
                'dsFio' => $fio,
                'dsMobPhone' => $phone->getValue(),
                'dsEmail' => $email->getValue(),
                'dsCity' => $city,
                'dsPickUpId' => $dsPickUpId,
            ],
        ];


        $result = $this->client->get(self::CREATE_ORDER, [
            'json' => $body,
        ]);

        $responseBody = $result->getBody()->getContents();

        dump($responseBody);die;
    }

    /**
     * @param string $orderIds
     *
     * @param string $extOrderIds
     * @return array
     */
    public function getOrderData(string $orderIds, string $extOrderIds): array
    {
        $body = [
            'query' => [
                'ApiKey' => $this->apiKey,
                'ExtOrderID' => $extOrderIds,
                'orderID' => $orderIds,
            ]
        ];

        $result = $this->client->get(self::GET_ORDER_DATA, [
            'json' => $body,
        ]);

        $responseBody = $result->getBody()->getContents();

        dump($responseBody);die;

//        $this->logger->writeExternalApiLog(
//            $client,
//            self::FIND_USER_NAME_INFO,
//            $result,
//            $responseBody,
//            $body,
//            $this->headers
//        );

//        return $this->getResponse($result, $responseBody);
    }

}