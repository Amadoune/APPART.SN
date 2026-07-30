<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum SeoSourceKind: string
{
    case Listing = 'listing';
    case Search = 'search';
    case Property = 'property';
}
