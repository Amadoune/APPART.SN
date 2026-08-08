<?php

namespace App\Providers;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerReader\OwnerLeadContactConsentReaderV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Contract\LeadContactConsentReaderV1;
use Illuminate\Support\ServiceProvider;

final class ContactsLeadsConsentOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OwnerLeadContactConsentReaderV1::class);
        $this->app->alias(OwnerLeadContactConsentReaderV1::class, LeadContactConsentReaderV1::class);
    }
}
