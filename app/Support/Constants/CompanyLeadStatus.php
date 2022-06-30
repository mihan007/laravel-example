<?php

namespace App\Support\Constants;

final class CompanyLeadStatus
{
    public const OK = 1;
    public const DONT_LEAVE = 2;
    public const WRONG_NUMBER = 3;
    public const ADS = 4;
    public const COULD_NOT_REACH_BY_PHONE = 5;
    public const DUPLICATE = 6;
    public const OUT_OF_SERVICE_AREA = 7;
    public const ANOTHER_REASON = 0;

    public const STATUSES = [
        self::OK => 'Целевая заявка',
        self::DONT_LEAVE => 'Не оставляли заявку',
        self::WRONG_NUMBER => 'Некорректный номер',
        self::ADS => 'Предложение рекламы',
        self::COULD_NOT_REACH_BY_PHONE => 'Не дозвонились',
        self::DUPLICATE => 'Дубль заявки',
        self::OUT_OF_SERVICE_AREA => 'Вне зоны обслуживания',
        self::ANOTHER_REASON => 'Другое',
    ];

    public const STATUSES_NOT_TARGET = [
        self::DONT_LEAVE,
        self::WRONG_NUMBER,
        self::ADS,
        self::COULD_NOT_REACH_BY_PHONE,
        self::DUPLICATE,
        self::OUT_OF_SERVICE_AREA,
        self::ANOTHER_REASON,
    ];

    public const STATUSES_TARGET = [
        self::OK,
    ];
    const STATUSES_WITH_REASON = [
        self::ANOTHER_REASON,
        self::DUPLICATE,
        self::OUT_OF_SERVICE_AREA,
    ];
}
