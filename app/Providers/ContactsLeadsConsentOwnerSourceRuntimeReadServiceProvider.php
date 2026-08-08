<?php

namespace App\Providers;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\DeterministicConsentOwnerSourceRuntimeReadV1;
use Illuminate\Support\ServiceProvider;

final class ContactsLeadsConsentOwnerSourceRuntimeReadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicConsentOwnerSourceRuntimeReadPolicy::class);
        $this->app->alias(
            DeterministicConsentOwnerSourceRuntimeReadPolicy::class,
            ConsentOwnerSourceRuntimeReadPolicy::class,
        );
        $this->app->singleton(DeterministicConsentOwnerSourceRuntimeReadV1::class);
        $this->app->alias(
            DeterministicConsentOwnerSourceRuntimeReadV1::class,
            ConsentOwnerSourceRuntimeReadV1::class,
        );
    }
}
