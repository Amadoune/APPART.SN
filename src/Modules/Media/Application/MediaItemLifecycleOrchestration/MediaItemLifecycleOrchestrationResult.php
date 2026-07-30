<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleOrchestration;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleDiagnostic;

final readonly class MediaItemLifecycleOrchestrationResult
{
    public function __construct(
        public MediaItemLifecycleOrchestrationStatus $status,
        public ?MediaItemLifecycleDiagnostic $diagnostic = null,
    ) {}
}
