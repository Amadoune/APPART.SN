<?php

namespace App\Application\ModerationHttp;

enum ModerationHttpOperation: string
{
    case SubmitReport = 'submit-report';
    case ValidateReport = 'validate-report';
    case RecordFinding = 'record-finding';
    case IssueDecision = 'issue-decision';
    case CloseCase = 'close-case';
    case ClaimQueueItem = 'claim-queue-item';
    case ReadOwnReport = 'read-own-report';
    case ReadQueue = 'read-queue';
    case ReadCase = 'read-case';
    case ReadDecision = 'read-decision';

    public function isMutation(): bool
    {
        return ! in_array($this, [
            self::ReadOwnReport,
            self::ReadQueue,
            self::ReadCase,
            self::ReadDecision,
        ], true);
    }
}
