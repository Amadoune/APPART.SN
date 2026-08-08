<?php

namespace Tests\Unit\LeadIngressContracts;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Command\SubmitLeadIngressV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Contract\LeadIngressCommandPortV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Contract\LeadIngressQueryPortV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Query\ReadOwnLeadIngressReceiptV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressPublicErrorV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressReadResultV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressReadStatusV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressReceiptV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressSubmissionResultV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result\LeadIngressSubmissionStatusV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressId;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressOccurredAt;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class LeadIngressContractsTest extends TestCase
{
    #[Test]
    public function submission_read_and_error_catalogues_are_closed(): void
    {
        self::assertSame([
            'accepted',
            'already_accepted',
            'rejected',
            'divergent_intent',
            'not_contactable',
            'contact_principal_unavailable',
            'recipient_not_eligible',
            'corrupted',
            'dependency_unavailable',
        ], array_column(LeadIngressSubmissionStatusV1::cases(), 'value'));
        self::assertSame([
            'found',
            'missing',
            'forbidden',
            'corrupted',
            'dependency_unavailable',
        ], array_column(LeadIngressReadStatusV1::cases(), 'value'));
        self::assertSame([
            'invalid_request',
            'not_contactable',
            'recipient_unavailable',
            'conflict',
            'not_found',
            'forbidden',
            'dependency_unavailable',
            'internal_failure',
        ], array_column(LeadIngressPublicErrorV1::cases(), 'value'));
    }

    #[Test]
    public function command_and_query_ports_are_minimal_and_versioned(): void
    {
        $submit = new ReflectionMethod(LeadIngressCommandPortV1::class, 'submit');
        self::assertSame(SubmitLeadIngressV1::class, (string) $submit->getParameters()[0]->getType());
        self::assertSame(LeadIngressSubmissionResultV1::class, (string) $submit->getReturnType());

        $read = new ReflectionMethod(LeadIngressQueryPortV1::class, 'readOwnReceipt');
        self::assertSame(ReadOwnLeadIngressReceiptV1::class, (string) $read->getParameters()[0]->getType());
        self::assertSame(LeadIngressReadResultV1::class, (string) $read->getReturnType());
    }

    #[Test]
    public function values_and_requests_are_immutable_with_explicit_canonical_time(): void
    {
        $observedAt = new LeadIngressObservedAt(new DateTimeImmutable('2026-07-31T10:15:30.123456+02:00'));
        $occurredAt = new LeadIngressOccurredAt(new DateTimeImmutable('2026-07-31T10:16:30.654321+02:00'));
        $intentId = LeadIngressIntentId::fromString('019428b8-5d5d-7c28-8a8f-8796c8732f91');
        $leadIngressId = LeadIngressId::fromString('019428b8-5d5d-7c28-8a8f-8796c8732f92');

        $command = new SubmitLeadIngressV1(
            $intentId,
            str_repeat('a', 64),
            '019428b8-5d5d-7c28-8a8f-8796c8732f93',
            'requester-ref',
            'contact-ref',
            'message-ref',
            $observedAt,
            $occurredAt,
        );
        $query = new ReadOwnLeadIngressReceiptV1($leadIngressId, 'requester-ref', $observedAt);

        self::assertTrue((new ReflectionClass($command))->isReadOnly());
        self::assertTrue((new ReflectionClass($query))->isReadOnly());
        self::assertSame('2026-07-31T08:15:30.123456Z', $observedAt->canonical());
        self::assertSame('2026-07-31T08:16:30.654321Z', $occurredAt->canonical());
    }

    #[Test]
    public function positive_results_alone_expose_their_minimal_payloads(): void
    {
        $id = LeadIngressId::fromString('019428b8-5d5d-7c28-8a8f-8796c8732f92');
        $recordedAt = new LeadIngressOccurredAt(new DateTimeImmutable('2026-07-31T08:16:30.654321Z'));
        $accepted = LeadIngressSubmissionResultV1::accepted($id);
        $alreadyAccepted = LeadIngressSubmissionResultV1::alreadyAccepted($id);
        $rejected = LeadIngressSubmissionResultV1::closed(LeadIngressSubmissionStatusV1::Rejected);
        $receipt = new LeadIngressReceiptV1($id, LeadIngressSubmissionStatusV1::Accepted, $recordedAt);
        $found = LeadIngressReadResultV1::found($receipt);
        $missing = LeadIngressReadResultV1::closed(LeadIngressReadStatusV1::Missing);

        self::assertSame($id, $accepted->leadIngressId);
        self::assertSame($id, $alreadyAccepted->leadIngressId);
        self::assertNull($rejected->leadIngressId);
        self::assertSame($receipt, $found->receipt);
        self::assertNull($missing->receipt);
    }

    #[Test]
    public function result_invariants_reject_positive_status_without_payload(): void
    {
        $submissionReflection = new ReflectionClass(LeadIngressSubmissionResultV1::class);
        $submissionConstructor = $submissionReflection->getConstructor();
        self::assertNotNull($submissionConstructor);

        try {
            $submissionConstructor->invoke(
                $submissionReflection->newInstanceWithoutConstructor(),
                LeadIngressSubmissionStatusV1::Accepted,
                null,
            );
            self::fail('Submission invariant was not enforced.');
        } catch (LogicException) {
            self::addToAssertionCount(1);
        }

        $readReflection = new ReflectionClass(LeadIngressReadResultV1::class);
        $readConstructor = $readReflection->getConstructor();
        self::assertNotNull($readConstructor);

        $this->expectException(LogicException::class);
        $readConstructor->invoke(
            $readReflection->newInstanceWithoutConstructor(),
            LeadIngressReadStatusV1::Found,
            null,
        );
    }
}
