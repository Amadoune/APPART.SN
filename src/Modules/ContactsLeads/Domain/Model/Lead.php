<?php

namespace Appart\Modules\ContactsLeads\Domain\Model;

use Appart\Modules\ContactsLeads\Domain\Event\AbstractLeadEvent;
use Appart\Modules\ContactsLeads\Domain\Event\LeadClosed;
use Appart\Modules\ContactsLeads\Domain\Event\LeadCreated;
use Appart\Modules\ContactsLeads\Domain\Event\LeadDelivered;
use Appart\Modules\ContactsLeads\Domain\Event\LeadEvent;
use Appart\Modules\ContactsLeads\Domain\Event\LeadRejected;
use Appart\Modules\ContactsLeads\Domain\Exception\LeadViolation;
use Appart\Modules\ContactsLeads\Domain\Policy\ContactChannelPolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentDecision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadEligibilityProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadRejectionReason;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadStatus;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;

final class Lead
{
    /** @var list<LeadEvent> */
    private array $events = [];

    /** @var list<LeadHistoryEntry> */
    private array $history = [];

    private int $version = 0;

    private function __construct(
        private readonly LeadId $id,
        private readonly ListingId $listingId,
        private readonly AdvertiserId $advertiserId,
        private readonly VisitorIdentity $visitor,
        private readonly ContactChannel $channel,
        private readonly ContactSubject $subject,
        private readonly ContactMessage $message,
        private readonly ConsentProof $consent,
        private readonly LeadEligibilityProof $eligibility,
        private LeadStatus $status,
        private LeadTimestamp $lastChangedAt,
    ) {}

    public static function create(LeadId $id, ListingId $listing, AdvertiserId $advertiser, VisitorIdentity $visitor, ContactChannel $channel, ContactSubject $subject, ContactMessage $message, ConsentProof $consent, LeadEligibilityProof $eligibility, LeadTimestamp $at, ContactChannelPolicy $channels): self
    {
        if ($consent->decision !== ConsentDecision::Granted || $consent->acceptedAt->value > $at->value) {
            throw new LeadViolation('Consent is required.');
        }
        $channels->assertAvailable($channel, $visitor);
        $self = new self($id, $listing, $advertiser, $visitor, $channel, $subject, $message, $consent, $eligibility, LeadStatus::Created, $at);
        $self->version = 1;
        $self->history[] = new LeadHistoryEntry(LeadStatus::Created, $at);
        $self->addEvent(new LeadCreated($id, $listing, $advertiser, $channel, $subject, $at));

        return $self;
    }

    /** @param list<LeadHistoryEntry> $history */
    public static function reconstitute(LeadId $id, ListingId $listing, AdvertiserId $advertiser, VisitorIdentity $visitor, ContactChannel $channel, ContactSubject $subject, ContactMessage $message, ConsentProof $consent, LeadEligibilityProof $eligibility, LeadStatus $status, LeadTimestamp $lastChangedAt, array $history, int $version): self
    {
        if ($version < 1 || $history === []) {
            throw new LeadViolation('Persisted lead state is invalid.');
        }
        $lead = new self($id, $listing, $advertiser, $visitor, $channel, $subject, $message, $consent, $eligibility, $status, $lastChangedAt);
        $lead->history = $history;
        $lead->version = $version;

        return $lead;
    }

    public function deliver(LeadTimestamp $at): void
    {
        $this->assertTransitionFrom(LeadStatus::Created, $at);
        $this->change(LeadStatus::Delivered, $at);
        $this->addEvent(new LeadDelivered($this->id, $this->listingId, $this->advertiserId, $at));
    }

    public function reject(LeadRejectionReason $reason, LeadTimestamp $at): void
    {
        $this->assertTransitionFrom(LeadStatus::Created, $at);
        $this->change(LeadStatus::Rejected, $at, $reason->value);
        $this->addEvent(new LeadRejected($this->id, $this->listingId, $this->advertiserId, $reason, $at));
    }

    public function close(LeadTimestamp $at): void
    {
        $this->assertMutable($at);
        if ($this->status !== LeadStatus::Delivered && $this->status !== LeadStatus::Rejected) {
            throw new LeadViolation('Only a delivered or rejected lead can be closed.');
        }
        $this->change(LeadStatus::Closed, $at);
        $this->addEvent(new LeadClosed($this->id, $this->listingId, $this->advertiserId, $at));
    }

    public function id(): LeadId
    {
        return $this->id;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function advertiserId(): AdvertiserId
    {
        return $this->advertiserId;
    }

    public function visitor(): VisitorIdentity
    {
        return $this->visitor;
    }

    public function channel(): ContactChannel
    {
        return $this->channel;
    }

    public function subject(): ContactSubject
    {
        return $this->subject;
    }

    public function message(): ContactMessage
    {
        return $this->message;
    }

    public function consent(): ConsentProof
    {
        return $this->consent;
    }

    public function eligibility(): LeadEligibilityProof
    {
        return $this->eligibility;
    }

    public function status(): LeadStatus
    {
        return $this->status;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<LeadHistoryEntry> */
    public function history(): array
    {
        return $this->history;
    }

    /** @return list<LeadEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function assertTransitionFrom(LeadStatus $expected, LeadTimestamp $at): void
    {
        $this->assertMutable($at);
        if ($this->status !== $expected) {
            throw new LeadViolation("Lead cannot transition from {$this->status->value}.");
        }
    }

    private function assertMutable(LeadTimestamp $at): void
    {
        if ($this->status === LeadStatus::Closed) {
            throw new LeadViolation('A closed lead is immutable.');
        }
        if ($at->isBefore($this->lastChangedAt)) {
            throw new LeadViolation('A lead transition cannot be backdated.');
        }
    }

    private function change(LeadStatus $status, LeadTimestamp $at, ?string $reason = null): void
    {
        $this->status = $status;
        $this->lastChangedAt = $at;
        $this->version++;
        $this->history[] = new LeadHistoryEntry($status, $at, $reason);
    }

    private function addEvent(LeadEvent $event): void
    {
        if ($event instanceof AbstractLeadEvent) {
            $event->stamp($this->version, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}
