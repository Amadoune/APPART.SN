<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

enum MediaBinaryObjectStoreStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentContent = 'divergent_content';
    case Missing = 'missing';
    case DependencyUnavailable = 'dependency_unavailable';
}
