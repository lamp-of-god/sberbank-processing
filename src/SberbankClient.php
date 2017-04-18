<?php

namespace LampOfGod\SberbankProcessing;

use Assert\Assertion;
use RestClient\Client;


/**
 * Implements Sberbank REST API for one-stage payment.
 */
class SberbankClient
{
    const SBERBANK_API_URL = 'https://securepayments.sberbank.ru/payment/rest';
    const SBERBANK_API_TEST_URL = 'https://3dsec.sberbank.ru/payment/rest/';

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
     * @param string $username  Sberbank API username.
     * @param string $password  Sberbank API password.
     * @param bool $test_mode   Shows whether test platform must be used or not.
     */
    public function __construct($username, $password, $test_mode = false)
    {
        Assertion::string($username);
        Assertion::string($password);

        $this->username = $username;
        $this->password = $password;
        $this->restClient = new Client(
            $test_mode
                ? static::SBERBANK_API_TEST_URL
                : static::SBERBANK_API_URL
        );
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
        if ($response['errorCode'] !== IRegisterOrderErrorCode::ERROR_NONE) {
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
        if ($response['ErrorCode'] !== IGetOrderStatusErrorCode::ERROR_NONE) {
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
