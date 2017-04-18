<?php

namespace LampOfGod\SberbankProcessing;


/**
 * Describes order statuses.
 */
interface IOrderStatus
{
    const ORDER_STATUS_REGISTERED = 0;
    const ORDER_STATUS_COMPLETED = 2;
    const ORDER_STATUS_CANCELED = 3;
    const ORDER_STATUS_REFUNDED = 4;
    const ORDER_STATUS_FAILED = 6;
}
