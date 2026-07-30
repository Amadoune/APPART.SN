<?php

namespace Tests\Unit\Application\PublicProjectionRetry;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionRetry\PublicProjectionDeterministicRetryPolicy;
use App\Application\PublicProjectionRetry\PublicProjectionExponentialBackoff;
use App\Application\PublicProjectionRetry\PublicProjectionFixedBackoff;
use App\Application\PublicProjectionRetry\PublicProjectionRetryDisposition;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PublicProjectionRetryPolicyTest extends TestCase
{
    public function test_fixed_and_exponential_backoffs_are_deterministic_and_capped(): void
    {
        $fixed = new PublicProjectionFixedBackoff(30);
        $exponential = new PublicProjectionExponentialBackoff(5, 20);

        self::assertSame(30, $fixed->forAttempt(PublicProjectionOutboxAttemptCount::fromInt(1))->delaySeconds);
        self::assertSame([5, 10, 20, 20], array_map(static fn (int $attempt): int => $exponential->forAttempt(PublicProjectionOutboxAttemptCount::fromInt($attempt))->delaySeconds, [1, 2, 3, 4]));
    }

    public function test_transient_retry_is_allowed_then_attempts_are_exhausted(): void
    {
        $policy = new PublicProjectionDeterministicRetryPolicy(3, new PublicProjectionFixedBackoff(15));
        $allowed = $policy->schedule(PublicProjectionOutboxRetryClassification::Transient, PublicProjectionOutboxAttemptCount::fromInt(2));
        $exhausted = $policy->schedule(PublicProjectionOutboxRetryClassification::Transient, PublicProjectionOutboxAttemptCount::fromInt(3));

        self::assertSame(PublicProjectionRetryDisposition::RetryAllowed, $allowed->disposition);
        self::assertSame('2026-07-19T10:00:15+00:00', $allowed->availableAt(new DateTimeImmutable('2026-07-19T10:00:00+00:00'))?->format('c'));
        self::assertSame(PublicProjectionRetryDisposition::AttemptsExhausted, $exhausted->disposition);
        self::assertFalse($exhausted->decision->retryAllowed);
        self::assertNull($exhausted->availableAt(new DateTimeImmutable));
    }

    public function test_permanent_is_denied_and_blockages_consume_no_retry_budget(): void
    {
        $policy = new PublicProjectionDeterministicRetryPolicy(3, new PublicProjectionFixedBackoff(15));

        self::assertSame(PublicProjectionRetryDisposition::RetryDenied, $policy->schedule(PublicProjectionOutboxRetryClassification::Permanent, PublicProjectionOutboxAttemptCount::fromInt(1))->disposition);
        self::assertSame(PublicProjectionRetryDisposition::BlockedWithoutAttemptBudget, $policy->schedule(PublicProjectionOutboxRetryClassification::SourceNotReady, PublicProjectionOutboxAttemptCount::fromInt(99))->disposition);
        self::assertSame(PublicProjectionRetryDisposition::BlockedWithoutAttemptBudget, $policy->schedule(PublicProjectionOutboxRetryClassification::SequenceGap, PublicProjectionOutboxAttemptCount::fromInt(99))->disposition);
    }
}
