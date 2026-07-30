<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum SeoIndexability: string
{
    case Indexable = 'indexable';
    case NotIndexable = 'not_indexable';
}
