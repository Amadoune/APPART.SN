<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum ExpiredListingTreatment: string
{
    case Remove = 'remove';
    case RetainNoIndex = 'retain_noindex';
    case NotApplicable = 'not_applicable';
}
