<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

final readonly class CredentialHashVerification
{
    public function __construct(public bool $verified, public bool $rehashRequired) {}
}
