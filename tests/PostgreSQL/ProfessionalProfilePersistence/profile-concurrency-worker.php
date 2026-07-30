<?php

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\PublicProfileVisibility;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalProfilePersistenceMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
$barrier = $arguments[1] ?? throw new RuntimeException('Missing barrier argument.');
$worker = $arguments[2] ?? throw new RuntimeException('Missing worker argument.');

touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$candidate = new ProfessionalPublicProfileState(
    '61000000-0000-4000-8000-000000000001',
    PublicProfileVisibility::Draft,
    'Agency',
    'Public description',
    ['agency'],
    ['fr'],
    ['phone' => '+221000000000'],
    [],
    1,
    1,
    'profile-v1',
    '61000000-0000-4000-8000-000000000041',
    str_repeat('a', 64),
    new DateTimeImmutable('2026-07-28T12:00:00+00:00'),
);

$store = new PostgreSqlProfessionalPublicProfileStore(
    PostgreSqlTestEnvironment::connection(),
    new ProfessionalProfilePersistenceMapper,
);

echo $store->save($candidate, 0)->value;
