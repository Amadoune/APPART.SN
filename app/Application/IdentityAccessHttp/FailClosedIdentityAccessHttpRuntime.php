<?php

namespace App\Application\IdentityAccessHttp;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use DateTimeImmutable;

final class FailClosedIdentityAccessHttpRuntime implements IdentityAccessHttpRuntime
{
    public function execute(IdentityAccessHttpCommand $command): IdentityAccessHttpResult
    {
        return new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
    }

    public function inspectSession(string $secret, DateTimeImmutable $at): IdentityAccessSessionInspection
    {
        return IdentityAccessSessionInspection::invalid();
    }
}
