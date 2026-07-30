<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect;

enum HistoricalRedirectDiagnosticCode: string
{
    case UnknownHistoricalCanonical = 'unknown_historical_canonical';
    case PublicDestinationMissing = 'public_destination_missing';
    case DestinationEqualsSource = 'destination_equals_source';
    case DestinationIsHistorical = 'destination_is_historical';
    case MultiplePublicDestinations = 'multiple_public_destinations';
    case StoredDecisionCorrupted = 'stored_decision_corrupted';
}
