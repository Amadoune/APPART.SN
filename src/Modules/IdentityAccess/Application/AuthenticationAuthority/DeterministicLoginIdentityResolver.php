<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

use Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract\LoginIdentitySourceV1;
use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Throwable;

final readonly class DeterministicLoginIdentityResolver implements LoginIdentityResolverV1
{
    public function __construct(private LoginIdentitySourceV1 $source) {}

    public function resolve(string $identifier): LoginIdentityResolution
    {
        try {
            $identity = str_starts_with(trim($identifier), '+')
                ? $this->source->byPhone(PhoneNumber::fromString($identifier)->value)
                : $this->source->byEmail(EmailAddress::fromString($identifier)->value);

            return $identity === null ? LoginIdentityResolution::notResolved() : LoginIdentityResolution::resolved($identity);
        } catch (InvalidIdentityValue) {
            return LoginIdentityResolution::notResolved();
        } catch (Throwable) {
            return LoginIdentityResolution::dependencyUnavailable();
        }
    }
}
