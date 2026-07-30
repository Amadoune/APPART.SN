<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum SearchSeoState: string
{
    case Public = 'public';
    case Hidden = 'hidden';
}
