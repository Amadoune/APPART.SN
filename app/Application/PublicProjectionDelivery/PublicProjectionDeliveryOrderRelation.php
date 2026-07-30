<?php

namespace App\Application\PublicProjectionDelivery;

enum PublicProjectionDeliveryOrderRelation: string
{
    case Equal = 'equal';
    case Before = 'before';
    case After = 'after';
    case Gap = 'gap';
    case Invalid = 'invalid';
}
