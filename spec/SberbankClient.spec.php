<?php

namespace LampOfGod\SberbankProcessing\Spec;

use Assert;
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
                    expect($closure)->toThrow();
                }
            }
        );

        it('throws an InvalidArgumentException if invalid password is given',
            function() {
                foreach ([null, [], new \stdClass()] as $password) {
                    $closure = function() use ($password) {
                        new SberbankClient('username', $password);
                    };
                    expect($closure)->toThrow();
                }
            }
        );

    });

});
