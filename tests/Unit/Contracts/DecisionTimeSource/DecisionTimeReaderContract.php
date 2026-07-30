<?php

namespace Tests\Unit\Contracts\DecisionTimeSource;

use App\Application\DecisionTimeSource\Contract\DecisionTimeReader;
use App\Application\DecisionTimeSource\DecisionTimeReadStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class DecisionTimeReaderContract extends TestCase
{
    protected const string LISTING = '99400000-0000-4000-8000-000000000001';

    abstract protected function reader(): DecisionTimeReader;

    abstract protected function givenDecisionAt(DateTimeImmutable $decisionAt): void;

    abstract protected function givenCorrupted(): void;

    public function test_missing_decision_time_is_explicit(): void
    {
        $result = $this->reader()->readByListing(self::LISTING);

        self::assertSame(DecisionTimeReadStatus::Missing, $result->status);
        self::assertNull($result->decisionAt);
    }

    public function test_exact_durable_decision_time_is_returned(): void
    {
        $expected = new DateTimeImmutable('2026-07-19T10:00:00.123456+00:00');
        $this->givenDecisionAt($expected);
        $result = $this->reader()->readByListing(self::LISTING);

        self::assertSame(DecisionTimeReadStatus::Found, $result->status);
        self::assertSame($expected, $result->decisionAt);
    }

    public function test_corrupted_decision_time_is_explicit(): void
    {
        $this->givenCorrupted();
        $result = $this->reader()->readByListing(self::LISTING);

        self::assertSame(DecisionTimeReadStatus::Corrupted, $result->status);
        self::assertNull($result->decisionAt);
    }
}
