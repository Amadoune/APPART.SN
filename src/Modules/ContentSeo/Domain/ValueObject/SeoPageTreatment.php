<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum SeoPageTreatment: string
{
    case Remove = 'remove';
    case Retain = 'retain';
}
