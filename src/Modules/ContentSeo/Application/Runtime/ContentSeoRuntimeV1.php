<?php

namespace Appart\Modules\ContentSeo\Application\Runtime;

interface ContentSeoRuntimeV1
{
    public function availability(): ContentSeoRuntimeAvailability;

    public function diagnostics(): ContentSeoRuntimeDiagnostics;
}
