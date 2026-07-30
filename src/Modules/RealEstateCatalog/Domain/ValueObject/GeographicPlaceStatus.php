<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

enum GeographicPlaceStatus: string
{
    case Usable = 'usable';
    case NotFound = 'not_found';
    case Disabled = 'disabled';
    case Merged = 'merged';
    case NotAddressable = 'not_addressable';
}
