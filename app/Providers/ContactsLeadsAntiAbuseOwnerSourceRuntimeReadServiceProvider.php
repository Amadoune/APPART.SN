<?php

namespace App\Providers;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\DeterministicLeadIngressAntiAbuseRuntimeReadV1;
use Illuminate\Support\ServiceProvider;

final class ContactsLeadsAntiAbuseOwnerSourceRuntimeReadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicLeadIngressAntiAbuseRuntimeReadPolicy::class);
        $this->app->alias(
            DeterministicLeadIngressAntiAbuseRuntimeReadPolicy::class,
            LeadIngressAntiAbuseRuntimeReadPolicy::class,
        );
        $this->app->singleton(DeterministicLeadIngressAntiAbuseRuntimeReadV1::class);
        $this->app->alias(
            DeterministicLeadIngressAntiAbuseRuntimeReadV1::class,
            LeadIngressAntiAbuseRuntimeReadV1::class,
        );
    }
}
