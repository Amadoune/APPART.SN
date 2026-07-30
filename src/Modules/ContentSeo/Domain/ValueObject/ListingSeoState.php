<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum ListingSeoState: string
{
    case Published = 'published';
    case Expired = 'expired';
    case NotPublished = 'not_published';
    case Terminal = 'terminal';
}
