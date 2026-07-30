<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

enum PropertyStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
