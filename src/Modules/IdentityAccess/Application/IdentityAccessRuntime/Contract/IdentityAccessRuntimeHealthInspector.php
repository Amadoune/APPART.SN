<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\Contract;

use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeHealthReport;

interface IdentityAccessRuntimeHealthInspector
{
    public function inspect(): IdentityAccessRuntimeHealthReport;
}
