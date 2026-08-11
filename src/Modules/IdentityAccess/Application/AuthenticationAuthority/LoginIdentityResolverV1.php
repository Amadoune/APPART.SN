<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

interface LoginIdentityResolverV1
{
    public function resolve(string $identifier): LoginIdentityResolution;
}
