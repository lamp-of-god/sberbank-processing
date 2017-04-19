<?php

namespace LampOfGod\SberbankProcessing;


/**
 * Describes order statuses.
 */
interface IOrderStatus
{
    const REGISTERED = 0;
    const COMPLETED  = 2;
    const CANCELED   = 3;
    const REFUNDED   = 4;
    const FAILED     = 6;
}
