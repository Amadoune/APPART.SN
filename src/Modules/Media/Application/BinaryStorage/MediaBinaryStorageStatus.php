<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

enum MediaBinaryStorageStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentContent = 'divergent_content';
    case DependencyUnavailable = 'dependency_unavailable';
}
