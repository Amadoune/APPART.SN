<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum PropertySeoState: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Archived = 'archived';
}
