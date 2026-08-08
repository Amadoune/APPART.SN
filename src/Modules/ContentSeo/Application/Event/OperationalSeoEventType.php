<?php

namespace Appart\Modules\ContentSeo\Application\Event;

enum OperationalSeoEventType: string
{
    case Observed = 'content_seo.operational_seo.observed.v1';
}
