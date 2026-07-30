<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime;

final readonly class IdentityAccessRuntimeRegistration
{
    public function __construct(
        public IdentityAccessRuntimeComponent $component,
        public bool $bound,
        public bool $compatible,
    ) {}
}
