<?php

namespace Tests\Feature;

use App\Application\ProfessionalEndpoint\Contract\ProfessionalEndpointRuntimeV1;
use App\Application\ProfessionalEndpoint\DeterministicProfessionalEndpointRuntimeV1;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProfessionalProfileHttpRuntimeTest extends TestCase
{
    #[Test]
    public function container_resolves_the_http_runtime_and_health_is_healthy(): void
    {
        $resolver = $this->createMock(ProfessionalMandateResolverV1::class);
        $resolver->method('resolve')->willReturn(ProfessionalMandateResolutionV1::notMandated());
        $reader = $this->createMock(ProfessionalPublicStatusReaderV1::class);
        $reader->method('read')->willReturn(ProfessionalPublicStatusDecisionV1::Missing);
        $this->app->instance(ProfessionalMandateResolverV1::class, $resolver);
        $this->app->instance(ProfessionalPublicStatusReaderV1::class, $reader);
        $this->app->instance(PDO::class, $this->createMock(PDO::class));

        $runtime = $this->app->make(ProfessionalEndpointRuntimeV1::class);

        self::assertInstanceOf(DeterministicProfessionalEndpointRuntimeV1::class, $runtime);
        self::assertSame($runtime, $this->app->make(ProfessionalEndpointRuntimeV1::class));
        self::assertSame(RuntimeHealthStatus::Healthy, $this->app->make(RuntimeHealthInspector::class)->inspect()->status);
    }
}
