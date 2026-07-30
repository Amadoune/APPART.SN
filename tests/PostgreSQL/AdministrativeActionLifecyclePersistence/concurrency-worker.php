<?php

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$worker = $argv[2];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$repository = new PostgreSqlAdministrativeActionLifecycleRepository(
    PostgreSqlTestEnvironment::connection(),
    new AdministrativeActionLifecycleWorkflowMapper,
    new AdministrativeActionEnrollmentCanonicalizer,
);
$result = $repository->append(AdministrativeActionHistoricalMirrorMutation::record(
    AdministrativeActionId::fromString('31000000-0000-4000-8000-000000000001'),
    1,
    new AdministrativeActionLifecycleTransition(
        AdministrativeActionLifecycleState::Draft,
        AdministrativeActionLifecycleState::Recorded,
        AdministrativeActionLifecycleAction::Record,
    ),
    ActorId::fromString('actor:contract-author'),
    AuditReason::fromString('Fixed contract evidence for the administrative action.'),
    AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-17T10:02:00Z')),
));
echo $result->value;
