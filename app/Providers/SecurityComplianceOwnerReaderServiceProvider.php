<?php

namespace App\Providers;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\ComplianceControlOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\IncidentOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\PrivacyPolicyOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecretInventoryOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecurityAuditOwnerReader;
use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecurityComplianceOwnerReaderPolicy;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\ComplianceControlReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\IncidentReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\PrivacyPolicyReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecretInventoryReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecurityAuditReaderV1;
use Illuminate\Support\ServiceProvider;

final class SecurityComplianceOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecurityComplianceOwnerReaderPolicy::class);
        $this->app->alias(SecurityComplianceOwnerReaderPolicy::class, SecurityComplianceOwnerReaderV1::class);
        $this->app->singleton(SecretInventoryOwnerReader::class);
        $this->app->alias(SecretInventoryOwnerReader::class, SecretInventoryReaderV1::class);
        $this->app->singleton(SecurityAuditOwnerReader::class);
        $this->app->alias(SecurityAuditOwnerReader::class, SecurityAuditReaderV1::class);
        $this->app->singleton(IncidentOwnerReader::class);
        $this->app->alias(IncidentOwnerReader::class, IncidentReaderV1::class);
        $this->app->singleton(PrivacyPolicyOwnerReader::class);
        $this->app->alias(PrivacyPolicyOwnerReader::class, PrivacyPolicyReaderV1::class);
        $this->app->singleton(ComplianceControlOwnerReader::class);
        $this->app->alias(ComplianceControlOwnerReader::class, ComplianceControlReaderV1::class);
    }
}
