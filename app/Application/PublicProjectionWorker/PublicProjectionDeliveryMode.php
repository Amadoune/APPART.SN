<?php

namespace App\Application\PublicProjectionWorker;

enum PublicProjectionDeliveryMode: string
{
    case Legacy = 'legacy';
    case RoutedV1 = 'routed-v1';
}
