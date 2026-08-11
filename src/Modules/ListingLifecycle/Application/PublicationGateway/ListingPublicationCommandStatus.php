<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationGateway;

enum ListingPublicationCommandStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case Missing = 'missing';
    case AuthorityUnavailable = 'authority_unavailable';
    case TransitionDenied = 'transition_denied';
    case DivergentCommand = 'divergent_command';
    case DependencyUnavailable = 'dependency_unavailable';
}
