<?php

namespace App\Providers;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerReader\OwnerLeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Contract\LeadIngressAntiAbuseReaderV1;
use Illuminate\Support\ServiceProvider;

final class ContactsLeadsAntiAbuseOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OwnerLeadIngressAntiAbuseReaderV1::class);
        $this->app->alias(OwnerLeadIngressAntiAbuseReaderV1::class, LeadIngressAntiAbuseReaderV1::class);
    }
}
