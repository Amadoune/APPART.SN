<?php

use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\RealEstateCatalog\FakePropertyRegistryHarness;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $mode, $barrier, $worker] = $argv;
$fixtures = new FakePropertyRegistryHarness;
$repository = new PostgreSqlPropertyRepository(PostgreSqlTestEnvironment::connection(), new PropertyMapper);
if ($mode === 'save') {
    $property = $repository->find($fixtures->primaryId());
} elseif ($mode === 'add_reference') {
    $property = $fixtures->minimalProperty(PropertyId::fromString(sprintf('33000000-0000-4000-8000-%012d', 200 + (int) $worker)), $fixtures->primaryReference());
} else {
    $property = $fixtures->minimalProperty();
}
if ($property === null) {
    exit(2);
}
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
try {
    if ($mode === 'save') {
        $surface = (int) $worker === 1 ? 140 : 160;
        $property->update(PropertyType::Villa, SurfaceArea::fromSquareMeters($surface), RoomCount::fromInt(6), BathroomCount::fromInt(3), ConstructionYear::fromInt(2020), BusinessYear::fromInt(2026), new PropertyTypePolicy, new DateTimeImmutable('2026-07-17T11:01:00+00:00'));
        $repository->save($property, 0);
    } else {
        $repository->add($property);
    }
    echo 'ok';
} catch (Throwable $error) {
    echo $error::class;
}
