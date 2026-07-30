<?php

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\IdentityAccess\AccountTestData;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
$barrier = $arguments[1] ?? throw new RuntimeException('Missing barrier argument.');
$worker = $arguments[2] ?? throw new RuntimeException('Missing worker argument.');
$account = AccountTestData::account();
$registry = new class($account) implements AccountRegistry
{
    public function __construct(private Account $account) {}

    public function find(AccountId $id): ?Account
    {
        return $this->account->id()->equals($id) ? clone $this->account : null;
    }

    public function add(Account $account): void {}

    public function save(Account $account, int $expectedVersion): void {}
};

touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$store = new PostgreSqlAccountStatusWorkflowStore(
    PostgreSqlTestEnvironment::connection(),
    $registry,
    new AccountStatusWorkflowMapper,
);

echo $store->bootstrap(AccountTestData::id())->value;
