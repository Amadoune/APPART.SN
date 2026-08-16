<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

enum GeographicPlaceAddressability: string
{
    case Addressable = 'addressable';
    case NotAddressable = 'not_addressable';
}
