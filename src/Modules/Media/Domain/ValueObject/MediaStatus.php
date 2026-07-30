<?php

namespace Appart\Modules\Media\Domain\ValueObject;

enum MediaStatus: string
{
    case Active = 'active';
    case Removed = 'removed';
    case Archived = 'archived';
}
