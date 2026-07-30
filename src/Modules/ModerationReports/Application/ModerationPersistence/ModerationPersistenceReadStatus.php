<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

enum ModerationPersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
