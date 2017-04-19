<?php

namespace LampOfGod\SberbankProcessing;


/**
 * Describes error codes of "register order" request.
 */
interface IRegisterOrderError
{
    const NONE               = 0;
    const ALREADY_REGISTERED = 1;
    const INCORRECT_CURRENCY = 3;
    const MISSED_PARAMETER   = 4;
    const MISSED_VALUE       = 5;
    const SYSTEM             = 7;
}
