<?php

namespace Appart\Modules\ContentSeo\Application\Runtime;

final readonly class DeterministicContentSeoRuntime implements ContentSeoRuntimeV1
{
    private const RUNTIME_ID = 'content-seo.owner-source';

    private const VERSION = 'content-seo-runtime-v1';

    public function __construct(private ContentSeoRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): ContentSeoRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): ContentSeoRuntimeDiagnostics
    {
        return new ContentSeoRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}
