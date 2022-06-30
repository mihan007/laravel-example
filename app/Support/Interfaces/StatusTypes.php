<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 11:20.
 */

namespace App\Support\Interfaces;

interface StatusTypes
{
    public const NOT_CONFIGURED = 1;
    public const COMPANY_RECONCILING = 2;
    public const USER_RECONCILING = 3;
    public const WAITING_FOR_PAYMENT = 4;
    public const NO_ORDERS = 5;
    public const PARTIALLY_PAID = 6;
    public const FULLY_PAID = 7;
}
