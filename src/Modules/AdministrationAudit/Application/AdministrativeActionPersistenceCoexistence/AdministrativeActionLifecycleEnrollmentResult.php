<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

enum AdministrativeActionLifecycleEnrollmentResult: string
{
    case Enrolled = 'enrolled';
    case AlreadyEnrolled = 'already_enrolled';
    case SourceMissing = 'source_missing';
    case SourceUnavailable = 'source_unavailable';
    case SourceCorrupted = 'source_corrupted';
    case EnrollmentDivergence = 'enrollment_divergence';
    case PersistenceCorrupted = 'persistence_corrupted';
}
