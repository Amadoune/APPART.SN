<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum RobotsPolicy: string
{
    case IndexFollow = 'index_follow';
    case NoIndexFollow = 'noindex_follow';
}
