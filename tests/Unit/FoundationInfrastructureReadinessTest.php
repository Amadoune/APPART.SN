<?php

namespace Tests\Unit;

use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionEvent;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\ContactsLeads\Domain\Event\LeadEvent;
use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContentSeo\Domain\Event\SeoEvent;
use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\IdentityAccess\Domain\Event\AccountEvent;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingEvent;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\Media\Domain\Event\MediaCollectionEvent;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\ModerationReports\Domain\Event\ModerationCaseEvent;
use Appart\Modules\ModerationReports\Domain\Model\ModerationCase;
use Appart\Modules\Professionals\Domain\Event\ProfessionalEvent;
use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\RealEstateCatalog\Domain\Event\PropertyEvent;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\SearchDiscovery\Domain\Event\SearchIndexEvent;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchIndex;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FoundationInfrastructureReadinessTest extends TestCase
{
    /** @param class-string $aggregate */
    #[DataProvider('reconstructibleAggregates')]
    public function test_official_reconstruction_entry_point_is_public_and_static(string $aggregate): void
    {
        $method = new ReflectionClass($aggregate)->getMethod('reconstitute');

        self::assertTrue($method->isPublic());
        self::assertTrue($method->isStatic());
    }

    /** @param class-string $eventContract */
    #[DataProvider('orderedEventContracts')]
    public function test_event_contract_exposes_ordering_metadata(string $eventContract): void
    {
        $contract = new ReflectionClass($eventContract);

        self::assertTrue($contract->hasMethod('aggregateVersion'));
        self::assertTrue($contract->hasMethod('eventIndex'));
    }

    /** @return iterable<string, array{class-string}> */
    public static function reconstructibleAggregates(): iterable
    {
        yield 'Account' => [Account::class];
        yield 'AdministrativeAction' => [AdministrativeAction::class];
        yield 'Professional' => [Professional::class];
        yield 'Property' => [Property::class];
        yield 'MediaCollection' => [MediaCollection::class];
        yield 'Listing' => [Listing::class];
        yield 'ModerationCase' => [ModerationCase::class];
        yield 'SearchIndex' => [SearchIndex::class];
        yield 'SeoProjection' => [SeoProjection::class];
        yield 'Lead' => [Lead::class];
    }

    /** @return iterable<string, array{class-string}> */
    public static function orderedEventContracts(): iterable
    {
        yield 'IdentityAccess' => [AccountEvent::class];
        yield 'AdministrationAudit' => [AdministrativeActionEvent::class];
        yield 'Professionals' => [ProfessionalEvent::class];
        yield 'RealEstateCatalog' => [PropertyEvent::class];
        yield 'Media' => [MediaCollectionEvent::class];
        yield 'ListingLifecycle' => [ListingEvent::class];
        yield 'ModerationReports' => [ModerationCaseEvent::class];
        yield 'SearchDiscovery' => [SearchIndexEvent::class];
        yield 'ContentSeo' => [SeoEvent::class];
        yield 'ContactsLeads' => [LeadEvent::class];
    }
}
