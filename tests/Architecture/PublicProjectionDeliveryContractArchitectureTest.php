<?php

namespace Tests\Architecture;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryContentSeoPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliverySearchPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class PublicProjectionDeliveryContractArchitectureTest extends TestCase
{
    public function test_contract_is_framework_runtime_and_infrastructure_independent(): void
    {
        foreach ($this->files() as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate\\|Laravel\\|PDO|PostgreSql|Repository|Controller|View|Route(?!d)|ServiceProvider|Worker|Dispatcher|Queue|Job|Listener|Outbox|Infrastructure\\|\bSQL\b)/i', $contents, $path);
        }
    }

    public function test_message_and_payload_implementations_are_specialized_and_immutable(): void
    {
        self::assertTrue((new ReflectionClass(PublicProjectionDeliveryMessage::class))->isReadOnly());

        foreach ($this->files() as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);
            self::assertTrue(
                str_starts_with($name, 'PublicProjectionDelivery')
                || str_starts_with($name, 'PublicProjectionRoutedDelivery'),
                $name,
            );
        }
    }

    public function test_payloads_are_closed_dtos_without_sensitive_or_aggregate_fields(): void
    {
        foreach ($this->payloadClasses() as $class) {
            $type = new ReflectionClass($class);
            self::assertTrue($type->isFinal());
            self::assertTrue($type->isReadOnly());
            self::assertTrue($type->implementsInterface(PublicProjectionDeliveryPayload::class));

            foreach ($type->getProperties() as $property) {
                self::assertDoesNotMatchRegularExpression('/(?:password|secret|token|hash|credential|aggregate)/i', $property->getName());
            }
        }
    }

    public function test_order_has_no_clock_or_message_identity_dependency(): void
    {
        $path = $this->root().DIRECTORY_SEPARATOR.'PublicProjectionDeliveryOrder.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertDoesNotMatchRegularExpression('/(?:DateTime|occurredAt|recordedAt|messageId|ULID|timestamp|time\s*\()/i', $contents);
    }

    public function test_fake_exists_only_under_tests(): void
    {
        self::assertFileExists(dirname(__DIR__).DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'Contracts'.DIRECTORY_SEPARATOR.'PublicProjectionDelivery'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'FakePublicProjectionDeliveryConsumer.php');
        foreach ($this->files() as $path) {
            self::assertStringNotContainsString('FakePublicProjectionDelivery', (string) file_get_contents($path));
        }
    }

    /** @return list<class-string<PublicProjectionDeliveryPayload>> */
    private function payloadClasses(): array
    {
        return [
            PublicProjectionDeliveryListingPayload::class,
            PublicProjectionDeliveryPropertyPayload::class,
            PublicProjectionDeliveryMediaPayload::class,
            PublicProjectionDeliverySearchPayload::class,
            PublicProjectionDeliveryContentSeoPayload::class,
        ];
    }

    /** @return list<string> */
    private function files(): array
    {
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root())) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function root(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'PublicProjectionDelivery';
    }
}
