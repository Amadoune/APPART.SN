<?php

namespace Appart\Modules\ModerationReports\Application\ReportOwnerReadSource;

enum ModerationReportOwnerReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
