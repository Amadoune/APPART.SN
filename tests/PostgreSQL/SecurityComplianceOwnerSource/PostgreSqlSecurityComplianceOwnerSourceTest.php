<?php

namespace Tests\PostgreSQL\SecurityComplianceOwnerSource;

use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryRevisionState;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryWriteResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditRevisionState;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\PostgreSql\PostgreSqlSecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Infrastructure\Persistence\SecurityComplianceOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSecurityComplianceOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlSecurityComplianceOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $sql = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/086_security_compliance_owner_source.sql');
        $this->connection->exec($sql);
        $this->connection->exec('TRUNCATE security_compliance.owner_current_index, security_compliance.owner_revision_journal');
        $this->source = new PostgreSqlSecurityComplianceOwnerSource($this->connection, new SecurityComplianceOwnerSourceMapper);
    }

    public function test_five_streams_are_independent_idempotent_and_temporal(): void
    {
        $effective = self::at('10:00:00');
        $recorded = self::at('10:00:01');
        $subject = new SecurityComplianceSubjectKey('scope:1');
        self::assertSame(SecretInventoryWriteResult::Applied, $this->source->appendSecretInventory(new SecretInventoryRevisionState($subject, 1, SecretInventoryStatusV1::Available, $effective, $recorded)));
        self::assertSame(SecretInventoryWriteResult::AlreadyApplied, $this->source->appendSecretInventory(new SecretInventoryRevisionState($subject, 1, SecretInventoryStatusV1::Available, $effective, $recorded)));
        $this->source->appendSecurityAudit(new SecurityAuditRevisionState($subject, 1, SecurityAuditStatusV1::Available, $effective, $recorded));
        $this->source->appendIncident(new IncidentRevisionState($subject, 1, IncidentStatusV1::Available, $effective, $recorded));
        $this->source->appendPrivacyPolicy(new PrivacyPolicyRevisionState($subject, 1, PrivacyPolicyStatusV1::Available, $effective, $recorded));
        $this->source->appendComplianceControl(new ComplianceControlRevisionState($subject, 1, ComplianceControlStatusV1::Available, $effective, $recorded));
        $observed = new SecurityComplianceObservedAt(self::at('11:00:00'));
        self::assertSame(SecretInventoryStatusV1::Available, $this->source->readSecretInventory($subject, $observed)->status);
        self::assertSame(SecurityAuditStatusV1::Available, $this->source->readSecurityAudit($subject, $observed)->status);
        self::assertSame(IncidentStatusV1::Available, $this->source->readIncident($subject, $observed)->status);
        self::assertSame(PrivacyPolicyStatusV1::Available, $this->source->readPrivacyPolicy($subject, $observed)->status);
        self::assertSame(ComplianceControlStatusV1::Available, $this->source->readComplianceControl($subject, $observed)->status);
    }

    public function test_external_rollback_is_preserved(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(SecretInventoryWriteResult::Applied, $this->source->appendSecretInventory(new SecretInventoryRevisionState('scope:rollback', 1, SecretInventoryStatusV1::Available, self::at('10:00:00'), self::at('10:00:01'))));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(SecretInventoryStatusV1::Missing, $this->source->readSecretInventory(new SecurityComplianceSubjectKey('scope:rollback'), new SecurityComplianceObservedAt(self::at('11:00:00')))->status);
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-04T'.$time.'.123456Z');
    }
}
