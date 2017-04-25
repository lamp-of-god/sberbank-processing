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

4) Check order status on callback page (specified on previous step):

        try {
            $status = $this->client->getOrderStatus($sber_id);
        } catch(\RuntimeException $e) {
            ...
            $status = IOrderStatus::FAILED;
        }
        
Note that situation when payment was rejected (for example by 3D secure) counts 
as error, so library throws an exception like as for any other error.
You may add additional check for exception reason by using exception code.

5) Do something depend on status:

        switch($status) {
        case IOrderStatus::COMPLETED:
        ...
        case IOrderStatus::FAILED:
        ...
        }
