<?php

use Appart\Modules\IdentityAccess\Domain\Exception\ConcurrentAccountModification;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $_SERVER['argv'][1] ?? null;
$number = $_SERVER['argv'][2] ?? null;
if (! is_string($barrier) || ! is_string($number)) {
    throw new RuntimeException('The concurrency worker arguments are required.');
}
$repository = new PostgreSqlAccountRepository(
    PostgreSqlTestEnvironment::connection(),
    new AccountPersistenceMapper,
);
$account = $repository->find(AccountId::fromString('49000000-0000-4000-8000-000000000001'));
if ($account === null) {
    throw new RuntimeException('Historical Account is missing.');
}
$expected = $account->version();
$account->changePassword(
    PasswordHash::fromString('$generic$v=1$worker-'.$number.'$'.str_repeat($number, 40)),
    new DateTimeImmutable('2026-07-26T09:10:00+00:00'),
);
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

try {
    $repository->save($account, $expected);
    echo 'saved';
} catch (ConcurrentAccountModification) {
    echo 'conflict';
}
