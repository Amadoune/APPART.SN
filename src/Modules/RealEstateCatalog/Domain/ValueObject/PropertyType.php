<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Villa = 'villa';
    case Land = 'land';
    case Office = 'office';
    case Commercial = 'commercial';
    case Other = 'other';
}
