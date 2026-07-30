<?php

namespace Tests\Unit\IdentityAccess\ModeratorAuthorization;

use App\Providers\ModeratorAuthorizationServiceProvider;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\OwnerModeratorAuthorizationReaderV1;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\IdentityAccess\Support\FakeAccountRegistry;

final class ModeratorAuthorizationRuntimeBindingTest extends TestCase
{
    #[Test]
    public function laravel_resolves_one_lazy_singleton_owner_reader(): void
    {
        $application = new Application(dirname(__DIR__, 5));
        $application->instance(AccountRegistry::class, new FakeAccountRegistry);
        (new ModeratorAuthorizationServiceProvider($application))->register();

        self::assertFalse($application->resolved(ModeratorAuthorizationReaderV1::class));
        $first = $application->make(ModeratorAuthorizationReaderV1::class);
        $second = $application->make(ModeratorAuthorizationReaderV1::class);

        self::assertInstanceOf(OwnerModeratorAuthorizationReaderV1::class, $first);
        self::assertSame($first, $second);
    }
}
