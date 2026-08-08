<?php

namespace Tests\Unit\ExperienceAcceptance;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceHttpResponseFactoryTest extends TestCase
{
    /** @return iterable<string, array{class-string, class-string, string, int, string}> */
    public static function cases(): iterable
    {
        $families = ['ResponsiveCompliance' => 'responsiveCompliance', 'AccessibilityCompliance' => 'accessibilityCompliance', 'UserExperience' => 'userExperience', 'EndToEndReadiness' => 'endToEndReadiness', 'PerformanceReadiness' => 'performanceReadiness', 'UserAcceptance' => 'userAcceptance', 'ReleaseCandidate' => 'releaseCandidate'];
        foreach ($families as $family => $method) {
            foreach (['Available' => 200, 'Missing' => 404, 'Corrupted' => 503, 'DependencyUnavailable' => 503] as $status => $code) {
                yield $family.' '.$status => ['Appart\\Modules\\ExperienceAcceptance\\Application\\PublicRead\\'.$family.'ResultV1', 'Appart\\Modules\\ExperienceAcceptance\\Application\\PublicRead\\'.$family.'StatusV1', $method, $code, $status];
            }
        }
    }

    #[DataProvider('cases')]
    public function test_mapping_is_mechanical(string $resultClass, string $statusClass, string $method, int $code, string $case): void
    {
        $response = (new ExperienceAcceptanceResponseFactory)->{$method}(new $resultClass(constant($statusClass.'::'.$case), new ExperienceAcceptanceObservedAt(new DateTimeImmutable('2026-08-08T10:00:00.123456+00:00'))));
        self::assertSame($code, $response->getStatusCode());
        self::assertSame(['status' => constant($statusClass.'::'.$case)->value, 'observedAt' => '2026-08-08T10:00:00.123456Z'], $response->getData(true));
    }
}
