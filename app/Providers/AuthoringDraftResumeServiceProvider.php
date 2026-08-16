<?php

namespace App\Providers;

use App\Application\AuthoringDraftResume\Contract\AuthoringDraftResumeReaderV1;
use App\Application\AuthoringDraftResume\DeterministicAuthoringDraftResumeReaderV1;
use Illuminate\Support\ServiceProvider;

final class AuthoringDraftResumeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicAuthoringDraftResumeReaderV1::class);
        $this->app->alias(DeterministicAuthoringDraftResumeReaderV1::class, AuthoringDraftResumeReaderV1::class);
    }
}
