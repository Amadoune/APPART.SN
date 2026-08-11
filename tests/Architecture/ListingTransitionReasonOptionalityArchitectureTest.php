<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingTransitionReasonOptionalityArchitectureTest extends TestCase
{
    public function test_additive_migration_relaxes_only_reason_nullability(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root.'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/092_listing_transition_reason_optional.sql');

        self::assertStringContainsString('ALTER COLUMN reason DROP NOT NULL', $migration);
        self::assertStringNotContainsString('UPDATE ', strtoupper($migration));
        self::assertStringNotContainsString('DELETE ', strtoupper($migration));
        self::assertSame('dc22316e49d410cd93f800c18fffc05d34acfb26fab2972a70bc979d1afd1f63', hash_file('sha256', $root.'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/002_listing.sql'));
    }

    public function test_policy_names_exactly_the_three_reasonless_triggers(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Domain/Policy/ListingTransitionPolicy.php');
        $guard = substr($policy, (int) strpos($policy, '$evidence->reason === null &&'), 350);

        self::assertStringContainsString('TransitionTrigger::SubmissionConfirmed', $guard);
        self::assertStringContainsString('TransitionTrigger::ReviewStarted', $guard);
        self::assertStringContainsString('TransitionTrigger::FavorableReview', $guard);
        self::assertSame(3, substr_count($guard, 'TransitionTrigger::'));
    }
}
