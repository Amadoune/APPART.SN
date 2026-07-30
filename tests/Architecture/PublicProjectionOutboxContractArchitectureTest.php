<?php

namespace Tests\Architecture;

use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class PublicProjectionOutboxContractArchitectureTest extends TestCase
{
    public function test_outbox_contract_has_no_framework_database_or_runtime_dependency(): void
    {
        foreach ($this->files() as $path) {
            $contents = file_get_contents($path);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate\\|Laravel\\|PDO|PostgreSql|Repository|Infrastructure\\|ServiceProvider|Worker|Dispatcher|Queue|Job|Listener|Controller|View|Route(?!d)|\bSQL\b|beginTransaction|commit\s*\(|rollBack)/i', $contents, $path);
        }
    }

    public function test_record_is_specialized_and_immutable(): void
    {
        self::assertTrue((new ReflectionClass(PublicProjectionOutboxRecord::class))->isFinal());
        self::assertTrue((new ReflectionClass(PublicProjectionOutboxRecord::class))->isReadOnly());

        foreach ($this->files() as $path) {
            self::assertStringStartsWith('PublicProjectionOutbox', pathinfo($path, PATHINFO_FILENAME));
        }
    }

    public function test_writer_and_reader_expose_no_crud_api(): void
    {
        $writer = array_map(static fn ($method): string => $method->getName(), (new ReflectionClass(PublicProjectionOutboxWriter::class))->getMethods());
        $reader = array_map(static fn ($method): string => $method->getName(), (new ReflectionClass(PublicProjectionOutboxReader::class))->getMethods());

        self::assertSame(['append', 'markDelivered', 'scheduleRetry', 'quarantine', 'releaseClaim'], $writer);
        foreach (['save', 'put', 'update', 'delete', 'all'] as $generic) {
            self::assertNotContains($generic, $writer);
            self::assertNotContains($generic, $reader);
        }
    }

    public function test_fakes_are_test_only(): void
    {
        $fakeRoot = dirname(__DIR__).DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'Contracts'.DIRECTORY_SEPARATOR.'PublicProjectionOutbox'.DIRECTORY_SEPARATOR.'Support';
        self::assertFileExists($fakeRoot.DIRECTORY_SEPARATOR.'FakePublicProjectionOutboxWriter.php');
        self::assertFileExists($fakeRoot.DIRECTORY_SEPARATOR.'FakePublicProjectionOutboxReader.php');
        self::assertFileExists($fakeRoot.DIRECTORY_SEPARATOR.'FakePublicProjectionOutboxClaimManager.php');
        self::assertFileExists($fakeRoot.DIRECTORY_SEPARATOR.'FakePublicProjectionOutboxCursorStore.php');

        foreach ($this->files() as $path) {
            self::assertStringNotContainsString('FakePublicProjectionOutbox', (string) file_get_contents($path));
        }
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
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'PublicProjectionOutbox';
    }
}
