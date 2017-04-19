<?php

namespace LampOfGod\SberbankProcessing;


/**
 * Describes error codes of "get order status" request.
 */
interface IGetOrderStatusError
{
    const NONE               = 0;
    const INCORRECT_PAYMENT  = 2;
    const ACCESS_DENIED      = 5;
    const UNREGISTERED_ORDER = 6;
    const SYSTEM             = 7;
}
