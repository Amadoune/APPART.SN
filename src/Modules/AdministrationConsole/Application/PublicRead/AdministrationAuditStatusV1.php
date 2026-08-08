<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

enum AdministrationAuditStatusV1: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
