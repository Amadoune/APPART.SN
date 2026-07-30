<?php

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistration;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxClaimManager;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionDeliveryClock;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionOutboxRetryPolicy;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $barrier, $workerNumber, $batchSize] = $argv;
$connection = PostgreSqlTestEnvironment::connection();
$consumer = new class($connection) implements PublicProjectionDeliveryConsumer
{
    public function __construct(private readonly PDO $connection) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        $statement = $this->connection->prepare('INSERT INTO listing_lifecycle.worker_test_effects(message_id) VALUES (:id) ON CONFLICT DO NOTHING');
        $statement->execute(['id' => $message->messageId->value]);
        usleep(200000);

        return $statement->rowCount() === 1 ? PublicProjectionDeliveryConsumptionResult::Consumed : PublicProjectionDeliveryConsumptionResult::AlreadyConsumed;
    }
};
$mapper = new PostgreSqlPublicProjectionOutboxMapper;
$consumerId = PublicProjectionOutboxConsumerId::fromString('public-projection');
$registry = new PublicProjectionDeliveryConsumerRegistry([
    new PublicProjectionDeliveryConsumerRegistration($consumerId, PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), $consumer),
]);
$worker = new PublicProjectionDeliveryWorker(new PostgreSqlPublicProjectionOutboxReader($connection, $mapper), new PostgreSqlPublicProjectionOutboxClaimManager($connection), new PostgreSqlPublicProjectionOutboxWriter($connection, $mapper), $registry, new FakePublicProjectionOutboxRetryPolicy, new FakePublicProjectionDeliveryClock(new DateTimeImmutable('now')), PublicProjectionDeliveryWorkerId::fromString('worker:'.$workerNumber), (int) $batchSize, 60);
touch($barrier.'.ready.'.$workerNumber);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
$result = $worker->runOnce($consumerId);
echo $result->claimed.':'.count($result->outcomes);
