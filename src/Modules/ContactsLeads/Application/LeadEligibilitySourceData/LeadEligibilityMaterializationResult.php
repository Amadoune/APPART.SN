<?php

namespace Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData;

enum LeadEligibilityMaterializationResult: string
{
    case Created = 'created';
    case AlreadyMaterialized = 'already_materialized';
    case StaleVersion = 'stale_version';
    case DivergentVersion = 'divergent_version';
    case IncoherentRevision = 'incoherent_revision';
    case RelationDivergence = 'relation_divergence';
    case ContinuityConflict = 'continuity_conflict';
}
