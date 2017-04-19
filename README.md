# Sberbank processing library

## Overview

Very simple small utility to work with Sberbank API.

It supports one-stage payment for russian rubles orders.

## How to use

1) Instantiate client:

        $client = new SberbankClient('username', 'password');


2) Register your order in Sberbank processing system:

        list($sberOrderID, $formURL) = $client->registerOrder(
            $order_id, $amount, 'http://return_url'
        );

3) Redirect user to the payment page (url received on previous step)

4) Periodically check order status:

        $status = $client->getOrderStatus($sberOrderID);

5) Do something when order get **COMPLETED** status:

        if ($status === IOrderStatus::COMPLETED) {
            ...
        }
