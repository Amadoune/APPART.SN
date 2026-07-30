<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessCompletionPersistenceArchitectureTest extends TestCase
{
    #[Test]
    public function migrations_are_additive_owner_scoped_and_do_not_reference_frozen_tables(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations';
        foreach (range(44, 51) as $number) {
            $files = glob($directory.'/0'.$number.'_*.sql');
            self::assertIsArray($files);
            $up = array_values(array_filter($files, static fn (string $file): bool => ! str_ends_with($file, '.down.sql')));
            self::assertCount(1, $up);
            $sql = (string) file_get_contents($up[0]);
            self::assertStringContainsString('identity_access_completion.', $sql);
            self::assertStringNotContainsString('REFERENCES identity_access.', $sql);
            self::assertStringNotContainsString('ON DELETE CASCADE', strtoupper($sql));
            self::assertStringNotContainsString('ALTER TABLE identity_access.', $sql);
        }
    }

    #[Test]
    public function persistence_slice_has_no_runtime_http_outbox_or_laravel_dependency(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/IdentityAccess';
        $files = array_merge(
            glob($directory.'/Application/IdentityAccessCompletionPersistence/**/*.php') ?: [],
            glob($directory.'/Application/IdentityAccessCompletionPersistence/*.php') ?: [],
            glob($directory.'/Infrastructure/Persistence/PostgreSql/*OwnerPersistenceStore.php') ?: [],
            glob($directory.'/Infrastructure/Persistence/PostgreSql/PostgreSql*Store.php') ?: [],
        );
        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            self::assertStringNotContainsString('Illuminate\\', $source);
            self::assertStringNotContainsString('\\Http\\', $source);
            self::assertStringNotContainsString('Outbox', $source);
            self::assertStringNotContainsString('Runtime', $source);
        }
    }
}
