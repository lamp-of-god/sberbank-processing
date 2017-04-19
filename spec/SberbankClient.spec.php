<?php

namespace LampOfGod\SberbankProcessing\Spec;

use LampOfGod\SberbankProcessing\IGetOrderStatusError;
use LampOfGod\SberbankProcessing\IOrderStatus;
use LampOfGod\SberbankProcessing\IRegisterOrderError;
use LampOfGod\SberbankProcessing\SberbankClient;


describe('SberbankClient', function() {

    describe('new SberbankClient()', function() {

        it('returns an instance of SberbankClient', function() {
            $client = new SberbankClient('username', 'password');
            expect($client)->toBeAnInstanceOf(SberbankClient::class);
        });

        it('throws an InvalidArgumentException if invalid username is given',
            function() {
                foreach ([null, [], new \stdClass()] as $username) {
                    $closure = function() use ($username) {
                        new SberbankClient($username, 'password');
                    };
                    expect($closure)->toThrow(new \InvalidArgumentException());
                }
            }
        );

        it('throws an InvalidArgumentException if invalid password is given',
            function() {
                foreach ([null, [], new \stdClass()] as $password) {
                    $closure = function() use ($password) {
                        new SberbankClient('username', $password);
                    };
                    expect($closure)->toThrow(new \InvalidArgumentException());
                }
            }
        );

    });

    describe('->registerOrder()', function() {

        beforeEach(function() {
            $this->client = new SberbankClient('username', 'password', true);
            $class = new \ReflectionClass($this->client);
            $method = $class->getMethod('makeAPIRequest');
            $method->setAccessible(true);
            allow($this->client)->toReceive('makeAPIRequest')->andReturn([]);
        });

        it('throws an InvalidArgumentException if order validation fails',
            function() {
                allow($this->client)
                    ->toReceive('::isOrderIDValid')
                    ->andReturn(false);
                $closure = function() {
                    $this->client->registerOrder('test', 100, 'http://test');
                };
                expect($closure)->toThrow(new \InvalidArgumentException());
            }
        );

        it('throws an InvalidArgumentException if invalid amount is given',
            function() {
                foreach ([null, [], new \stdClass(), 1.5] as $amount) {
                    $closure = function() use ($amount) {
                        $this->client
                             ->registerOrder(1, $amount, 'http://test');
                    };
                    expect($closure)->toThrow(new \InvalidArgumentException());
                }
            }
        );

        it('throws an InvalidArgumentException if invalid return URL',
            function() {
                foreach ([
                    null, [], new \stdClass(), 1.5, 'not-url'
                ] as $url) {
                    $closure = function() use ($url) {
                        $this->client->registerOrder(1, 100, $url);
                    };
                    expect($closure)->toThrow(new \InvalidArgumentException());
                }
            }
        );

        it('throws an correct Exception if request was unsuccessful',
            function() {
                foreach ([
                    IRegisterOrderError::ALREADY_REGISTERED,
                    IRegisterOrderError::INCORRECT_CURRENCY,
                    IRegisterOrderError::MISSED_PARAMETER,
                    IRegisterOrderError::MISSED_VALUE,
                    IRegisterOrderError::SYSTEM,
                ] as $errorCode) {
                    allow($this->client)->toReceive('makeAPIRequest')
                                        ->andReturn([
                                            'errorCode'    => $errorCode,
                                            'errorMessage' => 'test',
                                        ]);
                    $closure = function() {
                        $this->client->registerOrder(1, 100, 'http://test');
                    };
                    expect($closure)->toThrow(
                        new \RuntimeException('test', $errorCode)
                    );
                }
            }
        );

        it('returns an order ID and payment URL if request was successful',
            function() {
                allow($this->client)
                    ->toReceive('makeAPIRequest')
                    ->andReturn([
                        'orderId'   => 'testID',
                        'formUrl'   => 'http://url',
                        'errorCode' => IRegisterOrderError::NONE,
                    ]);
                $result = $this->client->registerOrder(1, 100, 'http://test');
                expect($result)->toBe(['testID', 'http://url']);
            }
        );

    });

    describe('->getOrderStatus()', function() {

        beforeEach(function() {
            $this->client = new SberbankClient('username', 'password', true);
            $class = new \ReflectionClass($this->client);
            $method = $class->getMethod('makeAPIRequest');
            $method->setAccessible(true);
            allow($this->client)->toReceive('makeAPIRequest')->andReturn([]);
        });

        it('throws an InvalidArgumentException if order validation fails',
            function() {
                allow($this->client)
                    ->toReceive('::isSberbankOrderIDValid')
                    ->andReturn(false);
                $closure = function() {
                    $this->client->getOrderStatus('test');
                };
                expect($closure)->toThrow(new \InvalidArgumentException());
            }
        );

        it('throws an correct Exception if request was unsuccessful',
            function() {
                foreach ([
                    IGetOrderStatusError::ACCESS_DENIED,
                    IGetOrderStatusError::INCORRECT_PAYMENT,
                    IGetOrderStatusError::UNREGISTERED_ORDER,
                    IGetOrderStatusError::SYSTEM,
                ] as $errorCode) {
                    allow($this->client)->toReceive('makeAPIRequest')
                                        ->andReturn([
                                            'ErrorCode'    => $errorCode,
                                            'ErrorMessage' => 'test',
                                        ]);
                    $closure = function() {
                        $this->client->getOrderStatus('test');
                    };
                    expect($closure)->toThrow(
                        new \RuntimeException('test', $errorCode)
                    );
                }
            }
        );

        it('returns an order status if request was successful', function() {
            allow($this->client)
                ->toReceive('makeAPIRequest')
                ->andReturn([
                    'OrderStatus' => IOrderStatus::COMPLETED,
                    'ErrorCode'   => IGetOrderStatusError::NONE,
                ]);
            $result = $this->client->getOrderStatus('test');
            expect($result)->toBe(IOrderStatus::COMPLETED);
        });

    });

    describe('::isOrderIDValid()', function() {

        it('returns true if correct order ID was given',
            function() {
                foreach ([1, 'NO-2534'] as $order_id) {
                    expect(SberbankClient::isOrderIDValid($order_id))
                        ->toBe(true);
                }
            }
        );

        it('returns false if incorrect order ID was given',
            function() {
                foreach ([
                    null, [], new \stdClass(), 1.5,
                    'very_long_order_name_more_than_32_symbols',
                ] as $order_id) {
                    expect(SberbankClient::isOrderIDValid($order_id))
                        ->toBe(false);
                }
            }
        );

    });

    describe('::isSberbankOrderIDValid()', function() {

        it('returns true if correct order ID was given',
            function() {
                expect(SberbankClient::isSberbankOrderIDValid(
                    '14613d21-71b4-45eb-81f9-12dc21a12253'
                ))->toBe(true);
            }
        );

        it('returns false if incorrect order ID was given',
            function() {
                foreach ([
                    null, [], new \stdClass(), 1.5,
                    'very_long_order_name_more_than_36_symbols',
                ] as $order_id) {
                    expect(SberbankClient::isSberbankOrderIDValid($order_id))
                        ->toBe(false);
                }
            }
        );

    });

});
