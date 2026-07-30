<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum CanonicalDisposition: string
{
    case Current = 'current';
    case ReservedForRedirect = 'reserved_for_redirect';
}
