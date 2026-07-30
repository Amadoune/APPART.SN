<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

enum AdministrativeActionHistoricalMirrorWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case MirrorDivergence = 'mirror_divergence';
    case EnrollmentDivergence = 'enrollment_divergence';
    case Corrupted = 'corrupted';
    case PersistenceCorrupted = 'persistence_corrupted';
}
