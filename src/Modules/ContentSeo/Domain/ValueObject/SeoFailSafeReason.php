<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

enum SeoFailSafeReason: string
{
    case SourceAbsent = 'source_absent';
    case InvalidContent = 'invalid_content';
    case InconsistentSources = 'inconsistent_sources';
}
