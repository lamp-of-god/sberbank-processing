<?php

namespace LampOfGod\SberbankProcessing;

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
        if (!is_string($username)) {
            throw new \InvalidArgumentException('Invalid username');
        }
        if (!is_string($password)) {
            throw new \InvalidArgumentException('Invalid password');
        }

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
     * @param int|string $order_id  Order ID to be registered.
     * @param int $amount           Order amount in kopeks.
     * @param string $return_url    Return URL to redirect user
     *                              after payment to.
     *
     * @throws \RuntimeException   When error during API request occured.
     *
     * @return string[]   Order ID in Sberbank system and URL to pay at.
     */
    public function registerOrder($order_id, $amount, $return_url)
    {
        if (!$this->isOrderIDValid($order_id)) {
            throw new \InvalidArgumentException('Invalid order ID');
        }
        if (!is_int($amount)) {
            throw new \InvalidArgumentException('Invalid amount');
        }
        if (!filter_var($return_url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid return URL');
        }

        $response = $this->makeAPIRequest('/register.do', [
            'orderNumber' => $order_id,
            'amount'      => $amount,
            'returnUrl'   => $return_url,
        ]);
        if ((int)$response['errorCode'] !== IRegisterOrderError::NONE) {
            throw new \RuntimeException(
                $response['errorMessage'], $response['errorCode']
            );
        }
        return [$response['orderId'], $response['formUrl']];
    }

    /**
     * Returns order payment status.
     *
     * @param string $sber_order_id   Order ID in Sberbank system.
     *
     * @throws \RuntimeException   When error during API request occured.
     *
     * @return int   Order status.
     */
    public function getOrderStatus($sber_order_id)
    {
        if (!$this->isSberbankOrderIDValid($sber_order_id)) {
            throw new \InvalidArgumentException('Invalid order ID');
        }

        $response = $this->makeAPIRequest('/getOrderStatus.do', [
            'orderId' => $sber_order_id,
        ]);
        if ((int)$response['ErrorCode'] !== IGetOrderStatusError::NONE) {
            throw new \RuntimeException(
                $response['ErrorMessage'], $response['ErrorCode']
            );
        }
        return (int)$response['OrderStatus'];
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
        return $response;
    }

    /**
     * Validates given order ID to fit Sberbank API requirements.
     *
     * @param int|string $order_id   Order ID to be validated.
     *
     * @return bool   Valid or not.
     */
    public static function isOrderIDValid($order_id)
    {
        if (!is_int($order_id) && !is_string($order_id)) {
            return false;
        }
        return (preg_match('/^[a-zA-Z0-9\-]{1,32}$/', $order_id) === 1);
    }

    /**
     * Validates given Sberbank order ID.
     *
     * @param string $order_id   Sberbank order ID to be validated.
     *
     * @return bool   Valid or not.
     */
    public static function isSberbankOrderIDValid($order_id)
    {
        if (!is_string($order_id)) {
            return false;
        }
        return (preg_match('/^[a-zA-Z0-9\-]{1,36}$/', $order_id) === 1);
    }
}
