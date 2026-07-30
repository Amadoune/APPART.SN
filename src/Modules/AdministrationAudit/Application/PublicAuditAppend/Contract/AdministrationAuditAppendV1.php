<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\Contract;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditRecordV1;

/**
 * Public append-only boundary owned by AdministrationAudit.
 *
 * Normative idempotence:
 * - a new recordId is Applied;
 * - the same recordId and checksum is AlreadyApplied;
 * - the same recordId with another checksum is DivergentRecord.
 *
 * Each invocation is committed in an AdministrationAudit owner-local
 * transaction. Callers never share or coordinate that transaction.
 */
interface AdministrationAuditAppendV1
{
    public function append(AdministrationAuditRecordV1 $record): AdministrationAuditAppendResultV1;
}
