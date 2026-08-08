<?php

namespace Appart\Modules\ContentSeo\Application\Event;

enum EditorialContentEventType: string
{
    case Observed = 'content_seo.editorial_content.observed.v1';
}
