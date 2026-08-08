<?php

namespace Appart\Modules\ContentSeo\Application\Runtime;

final readonly class ContentSeoRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public ContentSeoRuntimeAvailability $availability,
    ) {}
}
