<?php

namespace LampOfGod\SberbankProcessing;


/**
 * Describes error codes of "get order status" request.
 */
interface IGetOrderStatusErrorCode
{
    /**
     * Error code means that everything is ok.
     */
    const ERROR_NONE               = 0;
    const ERROR_INCORRECT_PAYMENT  = 2;
    const ERROR_ACCESS_DENIED      = 5;
    const ERROR_UNREGISTERED_ORDER = 6;
    const ERROR_SYSTEM             = 7;
}
