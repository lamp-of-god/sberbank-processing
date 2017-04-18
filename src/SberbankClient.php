<?php

namespace LampOfGod\SberbankClient;

use Assert\Assertion;
use RestClient\Client;


/**
 * Implements Sberbank REST API for one-stage payment.
 */
class SberbankClient
{
    /**
     * Error code means that everything is ok.
     */
    const ERRORCODE_OK = 0;

    /**
     * Possible order statuses.
     */
    const ORDER_STATUS_REGISTERED = 0;
    const ORDER_STATUS_COMPLETED = 2;
    const ORDER_STATUS_CANCELED = 3;
    const ORDER_STATUS_REFUNDED = 4;
    const ORDER_STATUS_FAILED = 6;


    const SBERBANK_API_URL = 'https://securepayments.sberbank.ru/payment/rest';

    /**
     * Sberbank API username.
     *
     * @var string
     */
    protected $username;

    /**
     * Sberbank API password.
     *
     * @var string
     */
    protected $password;

    /**
     * REST client used for work with Sberbank API.
     *
     * @var Client
     */
    protected $restClient;


    /**
     * SberbankClient constructor.
     *
     * @param string $username   Sberbank API username.
     * @param string $password   Sberbank API password.
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->restClient = new Client(static::SBERBANK_API_URL);
    }

    /**
     * Registers order with given params.
     *
     * @param int $order_id       Order ID to be registered.
     * @param int $amount         Order amount in kopeks.
     * @param string $return_url  Return URL to redirect user after payment.
     *
     * @throws \Exception   When error during API request occured.
     *
     * @return string   Order ID in Sberbank system.
     */
    public function registerOrder($order_id, $amount, $return_url)
    {
        Assertion::integer($order_id);
        Assertion::integer($amount);

        $response = $this->makeAPIRequest('/register.do', [
            'orderNumber' => $order_id,
            'amount'      => $amount,
            'returnUrl'   => $return_url,
        ]);
        if ($response['errorCode'] !== static::ERRORCODE_OK) {
            throw new \Exception(
                $response['errorMessage'], $response['errorCode']
            );
        }
        return $response['orderId'];
    }

    /**
     * Returns order payment status.
     *
     * @param string $sber_order_id   Order ID in Sberbank system.
     *
     * @throws \Exception   When error during API request occured.
     *
     * @return int   Order status.
     */
    public function getOrderStatus($sber_order_id)
    {
        Assertion::string($sber_order_id);

        $response = $this->makeAPIRequest('/getOrderStatus.do', [
            'orderId'   => $sber_order_id,
        ]);
        if ($response['ErrorCode'] !== static::ERRORCODE_OK) {
            throw new \LogicException(
                $response['ErrorMessage'], $response['ErrorCode']
            );
        }
        return $response['OrderStatus'];
    }

    /**
     * Performs API request to Sberbank processing API.
     *
     * @param string $url    URL to make request to (command).
     * @param array $params  Request-specific parameters.
     *
     * @throws \RuntimeException   When incorrect response was retrieved.
     *
     * @return array  API response.
     */
    protected function makeAPIRequest($url, $params)
    {
        $request = $this->restClient->newRequest($url, 'POST', [
            'userName' => $this->username,
            'password' => $this->password,
        ] + $params);

        $response = json_decode(
            $request->getResponse()->getParsedResponse(), true
        );
        if ($response === false) {
            throw new \RuntimeException('Invalid API response');
        }
    }
}
