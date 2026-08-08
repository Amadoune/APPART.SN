<?php

namespace Tests\Unit\AdministrationConsole\Http;

use App\Http\AdministrationConsole\AdministrationConsoleResponseFactory;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueResultV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrationConsoleResponseFactoryTest extends TestCase
{
    #[DataProvider('mappings')]
    public function test_mapping_is_exhaustive(string $kind, object $result, int $expected): void
    {
        $response = (new AdministrationConsoleResponseFactory)->{$kind}($result);

        self::assertSame($expected, $response->getStatusCode());
        self::assertSame(['status' => $result->status->value], $response->getData(true));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    /** @return iterable<string, array{string, AdministrationOperatorResultV1|AdministrationQueueResultV1|AdministrationAuditResultV1, int}> */
    public static function mappings(): iterable
    {
        foreach ([[AdministrationOperatorStatusV1::Available, 200], [AdministrationOperatorStatusV1::Unavailable, 200], [AdministrationOperatorStatusV1::Missing, 404], [AdministrationOperatorStatusV1::Corrupted, 503], [AdministrationOperatorStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'operator '.$status->value => ['operator', new AdministrationOperatorResultV1($status), $http];
        }
        foreach ([[AdministrationQueueStatusV1::Ready, 200], [AdministrationQueueStatusV1::Empty, 200], [AdministrationQueueStatusV1::Missing, 404], [AdministrationQueueStatusV1::Corrupted, 503], [AdministrationQueueStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'queue '.$status->value => ['queue', new AdministrationQueueResultV1($status), $http];
        }
        foreach ([[AdministrationAuditStatusV1::Available, 200], [AdministrationAuditStatusV1::Missing, 404], [AdministrationAuditStatusV1::Corrupted, 503], [AdministrationAuditStatusV1::DependencyUnavailable, 503]] as [$status, $http]) {
            yield 'audit '.$status->value => ['audit', new AdministrationAuditResultV1($status), $http];
        }
    }
}
