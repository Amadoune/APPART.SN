<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

enum IncidentStatusV1: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
