<?php

namespace App\Gateways;

interface RankerGateway
{
    public const MAX_RETRY = 3;
    public const TIMEOUT = 5000;

    function rank(array $items);
}
