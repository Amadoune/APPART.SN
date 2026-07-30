<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract\ProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\OwnerProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Infrastructure\Runtime\OwnerProfessionalPublicStatusReaderV1;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;

final class ProfessionalOwnerReadRuntimeBindingsTest extends TestCase
{
    #[Test]
    public function container_resolves_unique_lazy_singletons_and_runtime_health_is_healthy(): void
    {
        $this->app->instance(PDO::class, $this->createMock(PDO::class));

        $resolver = $this->app->make(ProfessionalMandateResolverV1::class);
        $reader = $this->app->make(ProfessionalPublicStatusReaderV1::class);

        self::assertInstanceOf(OwnerProfessionalMandateResolverV1::class, $resolver);
        self::assertInstanceOf(OwnerProfessionalPublicStatusReaderV1::class, $reader);
        self::assertSame($resolver, $this->app->make(ProfessionalMandateResolverV1::class));
        self::assertSame($reader, $this->app->make(ProfessionalPublicStatusReaderV1::class));
        self::assertSame(
            $this->app->make(ProfessionalMandateOwnerSource::class),
            (new ReflectionProperty($resolver, 'source'))->getValue($resolver),
        );
        self::assertSame(
            $this->app->make(ProfessionalStatusWorkflowStore::class),
            (new ReflectionProperty($reader, 'store'))->getValue($reader),
        );
        self::assertSame(RuntimeHealthStatus::Healthy, $this->app->make(RuntimeHealthInspector::class)->inspect()->status);
    }
}
