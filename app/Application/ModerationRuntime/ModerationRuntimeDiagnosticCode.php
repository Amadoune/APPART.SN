<?php

namespace App\Application\ModerationRuntime;

enum ModerationRuntimeDiagnosticCode: string
{
    case CaseStoreMissing = 'case_store_missing';
    case DecisionStoreMissing = 'decision_store_missing';
    case QueueStoreMissing = 'queue_store_missing';
}
