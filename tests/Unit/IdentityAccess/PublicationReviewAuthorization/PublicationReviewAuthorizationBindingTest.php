<?php

namespace Tests\Unit\IdentityAccess\PublicationReviewAuthorization;

use App\Providers\PublicationReviewAuthorizationServiceProvider;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract\PublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\OwnerPublicationReviewAuthorizationReaderV1;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\IdentityAccess\Support\FakeAccountRegistry;

final class PublicationReviewAuthorizationBindingTest extends TestCase
{
    public function test_named_reader_binding_is_a_lazy_singleton(): void
    {
        $application = new Application(dirname(__DIR__, 5));
        $application->instance(AccountRegistry::class, new FakeAccountRegistry);
        (new PublicationReviewAuthorizationServiceProvider($application))->register();

        self::assertFalse($application->resolved(PublicationReviewAuthorizationReaderV1::class));
        $first = $application->make(PublicationReviewAuthorizationReaderV1::class);
        self::assertInstanceOf(OwnerPublicationReviewAuthorizationReaderV1::class, $first);
        self::assertSame($first, $application->make(PublicationReviewAuthorizationReaderV1::class));
    }
}
