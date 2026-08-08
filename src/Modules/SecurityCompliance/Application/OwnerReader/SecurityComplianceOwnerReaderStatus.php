<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

enum SecurityComplianceOwnerReaderStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
