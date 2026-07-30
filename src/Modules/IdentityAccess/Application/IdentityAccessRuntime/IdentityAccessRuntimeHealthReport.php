<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime;

final readonly class IdentityAccessRuntimeHealthReport
{
    /** @param list<IdentityAccessRuntimeComponent> $unhealthyComponents */
    public function __construct(
        public IdentityAccessRuntimeHealthStatus $status,
        public array $unhealthyComponents,
    ) {}
}
