<?php

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicOperation;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlIdentityAccessAtomicTransaction;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$connection = PostgreSqlTestEnvironment::connection();
$transaction = new PostgreSqlIdentityAccessAtomicTransaction($connection);
$command = new IdentityAccessAtomicCommand(
    'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    str_repeat('a', 64),
    AccountId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
    IdentityAccessAtomicOperation::Authentication,
    new DateTimeImmutable('2026-01-01T00:00:00Z'),
);

$result = $transaction->execute($command, static function () use ($connection): IdentityAccessAtomicWorkResult {
    $statement = $connection->prepare(
        'INSERT INTO identity_access_completion.authentication_attempts
         (attempt_key,policy_version,failure_count,version,last_intent_id,last_intent_checksum,last_outcome,updated_at)
         VALUES(:attempt_key,:policy_version,1,1,CAST(:intent_id AS uuid),:checksum,:outcome,now())',
    );
    $statement->execute([
        'attempt_key' => 'atomic-concurrent-key',
        'policy_version' => 'v1',
        'intent_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        'checksum' => str_repeat('c', 64),
        'outcome' => 'Rejected',
    ]);

    return IdentityAccessAtomicWorkResult::Applied;
});

echo $result->status->value;
