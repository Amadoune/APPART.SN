<?php

use Appart\Modules\IdentityAccess\Application\IdentityAccessCompletionPersistence\OwnerPersistenceState;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthenticationAttemptStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$store = new PostgreSqlAuthenticationAttemptStore(
    PostgreSqlTestEnvironment::connection(),
    new IdentityAccessCompletionPersistenceMapper,
);
echo $store->save(new OwnerPersistenceState(
    'concurrent-login-key',
    1,
    'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
    str_repeat('9', 64),
    [
        'policy_version' => 'v1',
        'failure_count' => 1,
        'window_started_at' => '2026-01-01T00:00:00Z',
        'locked_until' => null,
        'last_outcome' => 'Rejected',
        'updated_at' => '2026-01-01T00:00:00Z',
    ],
), 0)->value;
