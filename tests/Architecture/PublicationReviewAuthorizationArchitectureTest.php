<?php

namespace Tests\Architecture;

use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract\PublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class PublicationReviewAuthorizationArchitectureTest extends TestCase
{
    public function test_authority_is_framework_agnostic_and_has_no_cross_domain_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/IdentityAccess/Application/PublicationReviewAuthorization/**/*.php') ?: [];
        $files = array_merge($files, glob($root.'/src/Modules/IdentityAccess/Application/PublicationReviewAuthorization/*.php') ?: []);
        self::assertNotEmpty($files);

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PDO', 'PostgreSql', 'Illuminate\\', 'Http\\', 'Modules\\PublicationReview', 'ListingLifecycle', 'RealEstateCatalog', 'Modules\\Media', 'Search'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_reader_accepts_only_the_canonical_session_account_identity(): void
    {
        $method = new ReflectionMethod(PublicationReviewAuthorizationReaderV1::class, 'authorize');
        $parameters = $method->getParameters();

        self::assertCount(3, $parameters);
        self::assertSame(AccountId::class, (string) $parameters[0]->getType());
        self::assertSame('accountId', $parameters[0]->getName());
    }
}
