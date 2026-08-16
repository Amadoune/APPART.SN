<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

enum PromoteAuthoredPropertyStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case AuthoringMissing = 'authoring_missing';
    case OwnershipMismatch = 'ownership_mismatch';
    case IncompleteAuthoring = 'incomplete_authoring';
    case VersionConflict = 'version_conflict';
    case DomainRejected = 'domain_rejected';
    case DependencyUnavailable = 'dependency_unavailable';
    case DivergentCommand = 'divergent_command';
}
