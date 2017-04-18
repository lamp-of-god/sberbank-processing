<?php

namespace LampOfGod\SberbankProcessing;


/**
 * Describes error codes of "register order" request.
 */
interface IRegisterOrderErrorCode
{
    /**
     * Error code means that everything is ok.
     */
    const ERROR_NONE               = 0;
    const ERROR_ALREADY_REGISTERED = 1;
    const ERROR_INCORRECT_CURRENCY = 3;
    const ERROR_MISSED_PARAMETER   = 4;
    const ERROR_MISSED_VALUE       = 5;
    const ERROR_SYSTEM             = 7;
}
