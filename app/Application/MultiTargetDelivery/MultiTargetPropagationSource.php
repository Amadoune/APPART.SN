<?php

namespace App\Application\MultiTargetDelivery;

enum MultiTargetPropagationSource: string
{
    case Property = 'property';
    case Media = 'media';
}
