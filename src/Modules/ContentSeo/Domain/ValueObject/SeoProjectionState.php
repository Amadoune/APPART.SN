<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum SeoProjectionState: string
{
    case Active = 'active';
    case Removed = 'removed';
}
