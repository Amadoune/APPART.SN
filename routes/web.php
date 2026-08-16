<?php

use App\Application\IdentityAccessHttp\IdentityAccessHttpOperation;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpOperation;
use App\Application\ModerationHttp\ModerationHttpOperation;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpOperation;
use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Http\Controllers\AccountStatusHttpController;
use App\Http\Controllers\AdministrativeActionLifecycleHttpController;
use App\Http\Controllers\AuthoringWorkspaceController;
use App\Http\Controllers\IdentityAccessHttpController;
use App\Http\Controllers\LeadLifecycleHttpController;
use App\Http\Controllers\ListingPublicationTransitionController;
use App\Http\Controllers\MediaAuthoringHttpController;
use App\Http\Controllers\MediaItemLifecycleHttpController;
use App\Http\Controllers\ModerationHttpController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\PlaceLifecycleHttpController;
use App\Http\Controllers\ProfessionalProfileHttpController;
use App\Http\Controllers\ProfessionalStatusHttpController;
use App\Http\Controllers\PropertyLifecycleTransitionController;
use App\Http\Controllers\PropertyAuthoringGeographySelectionController;
use App\Http\Controllers\PropertyListingAuthoringHttpController;
use App\Http\Controllers\PublicationReviewExperienceController;
use App\Http\Controllers\PublicAuthoringJourneyController;
use App\Http\Controllers\PublicListingController;
use App\Http\Controllers\PublicMediaBinaryController;
use App\Http\Controllers\PublicSearchExperienceController;
use App\Http\Controllers\PublicSitemapController;
use App\Http\Controllers\ReservationLifecycleHttpController;
use App\Http\Middleware\RequireAccountStatusLifecycleAuthority;
use App\Http\Middleware\RequireIdentityAccessSession;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function (PublicSearchResultsReaderV1 $results) {
    return view('home', ['publicListings' => $results->read(new PublicSearchResultsQuery(3))->items]);
})->name('home');

Route::view('/connexion', 'iam-login')->name('iam-web-entry.login');

Route::get('/recherche', PublicSearchExperienceController::class)
    ->name('public-search.experience');

Route::get('/espace-proprietaire', OwnerDashboardController::class)
    ->name('owner-dashboard');

Route::get('/sitemap.xml', PublicSitemapController::class)
    ->name('public-sitemap');

Route::get('/media/{mediaId}/revisions/{assetVersion}', PublicMediaBinaryController::class)
    ->whereUuid('mediaId')
    ->whereNumber('assetVersion')
    ->name('public-media.binary');

Route::get('/_local/bootstrap', function () {
    abort_unless(app()->environment('local'), 404);

    $postgresql = 'unavailable';

    try {
        DB::select('SELECT 1');
        $postgresql = 'connected';
    } catch (Throwable) {
        // The bootstrap page reports availability without exposing diagnostics.
    }

    return view('local-bootstrap', [
        'environment' => app()->environment(),
        'laravelVersion' => app()->version(),
        'phpVersion' => PHP_VERSION,
        'postgresql' => $postgresql,
    ]);
})->name('local-bootstrap');

Route::post('/api/listing-publications/{listingId}/transitions', ListingPublicationTransitionController::class)
    ->whereUuid('listingId')
    ->name('listing-publication.transition');

Route::post('/api/property-lifecycles/{propertyId}/transitions', PropertyLifecycleTransitionController::class)
    ->whereUuid('propertyId')
    ->name('property-lifecycle.transition');

Route::post('/api/reservation-lifecycles/{reservationId}/transitions', ReservationLifecycleHttpController::class)
    ->whereUuid('reservationId')
    ->name('reservation-lifecycle.transition');

Route::post('/api/lead-lifecycles/{leadId}/transitions', LeadLifecycleHttpController::class)
    ->whereUuid('leadId')
    ->name('lead-lifecycle.transition');

Route::post('/api/professional-statuses/{professionalId}/transitions', ProfessionalStatusHttpController::class)
    ->whereUuid('professionalId')
    ->name('professional-status.transition');

Route::post('/api/media-item-lifecycles/{mediaId}/transitions', MediaItemLifecycleHttpController::class)
    ->whereUuid('mediaId')
    ->name('media-item-lifecycle.transition');

Route::post('/api/administrative-action-lifecycles/{actionId}/transitions', AdministrativeActionLifecycleHttpController::class)
    ->whereUuid('actionId')
    ->name('administrative-action-lifecycle.transition');

Route::post('/api/place-lifecycles/{placeId}/transitions', PlaceLifecycleHttpController::class)
    ->whereUuid('placeId')
    ->name('place-lifecycle.transition');

Route::middleware(RequireAccountStatusLifecycleAuthority::class)
    ->group(function (): void {
        Route::post('/api/account-statuses/{accountId}/suspend', [AccountStatusHttpController::class, 'suspend'])
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->whereUuid('accountId')
            ->name('account-status.suspend');

        Route::post('/api/account-statuses/{accountId}/reactivate', [AccountStatusHttpController::class, 'reactivate'])
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->whereUuid('accountId')
            ->name('account-status.reactivate');
    });

Route::prefix('/api/identity-access')->group(function (): void {
    Route::post('/login', IdentityAccessHttpController::class)
        ->middleware('throttle:iam-login')
        ->defaults('iam_operation', IdentityAccessHttpOperation::Login->value)
        ->name('identity-access.login');
    Route::post('/password-recovery', IdentityAccessHttpController::class)
        ->middleware('throttle:iam-recovery')
        ->defaults('iam_operation', IdentityAccessHttpOperation::RequestRecovery->value)
        ->name('identity-access.recovery.request');
    Route::post('/password-recovery/complete', IdentityAccessHttpController::class)
        ->middleware('throttle:iam-recovery')
        ->defaults('iam_operation', IdentityAccessHttpOperation::CompleteRecovery->value)
        ->name('identity-access.recovery.complete');

    Route::middleware([RequireIdentityAccessSession::class, 'throttle:iam-authenticated'])
        ->group(function (): void {
            Route::post('/logout', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::Logout->value)
                ->name('identity-access.logout');
            Route::post('/sessions/renew', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::RenewSession->value)
                ->name('identity-access.sessions.renew');
            Route::get('/sessions', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::ListSessions->value)
                ->name('identity-access.sessions.index');
            Route::get('/profile', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::ReadProfile->value)
                ->name('identity-access.profile.show');
            Route::patch('/profile', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::MutateProfile->value)
                ->name('identity-access.profile.update');
            Route::post('/profile/contact-changes', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::RequestContactChange->value)
                ->name('identity-access.contact-change.request');
            Route::post('/profile/contact-changes/verify', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::VerifyContactChange->value)
                ->name('identity-access.contact-change.verify');
            Route::post('/closure', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::RequestClosure->value)
                ->name('identity-access.closure.request');
            Route::post('/closure/confirm', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::ConfirmClosure->value)
                ->name('identity-access.closure.confirm');
            Route::post('/reopen', IdentityAccessHttpController::class)
                ->defaults('iam_operation', IdentityAccessHttpOperation::Reopen->value)
                ->name('identity-access.reopen');
        });
});

Route::prefix('/api/authoring')
    ->middleware([RequireIdentityAccessSession::class, 'throttle:property-listing-authoring'])
    ->group(function (): void {
        Route::get('/geography/selections', PropertyAuthoringGeographySelectionController::class)
            ->name('property-authoring.geography-selections');
        Route::post('/properties/{propertyId}', PropertyListingAuthoringHttpController::class)
            ->where('propertyId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::InitiateProperty->value);
        Route::get('/properties/{propertyId}', PropertyListingAuthoringHttpController::class)
            ->where('propertyId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::ReadProperty->value);
        Route::patch('/properties/{propertyId}', PropertyListingAuthoringHttpController::class)
            ->where('propertyId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::PatchProperty->value);
        Route::post('/properties/{propertyId}/listings', PropertyListingAuthoringHttpController::class)
            ->where('propertyId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::CreateListing->value);
        Route::get('/listings/{listingId}/draft', PropertyListingAuthoringHttpController::class)
            ->where('listingId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::ReadDraft->value);
        Route::patch('/listings/{listingId}/draft', PropertyListingAuthoringHttpController::class)
            ->where('listingId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::PatchDraft->value);
        Route::get('/listings/{listingId}/completeness', PropertyListingAuthoringHttpController::class)
            ->where('listingId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::AssessCompleteness->value);
        Route::put('/listings/{listingId}/delegations', PropertyListingAuthoringHttpController::class)
            ->where('listingId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::GrantDelegation->value);
        Route::delete('/listings/{listingId}/delegations', PropertyListingAuthoringHttpController::class)
            ->where('listingId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::RevokeDelegation->value);
        Route::post('/listings/{listingId}/submission', PropertyListingAuthoringHttpController::class)
            ->where('listingId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::RequestSubmission->value);
        Route::get('/portfolio', PropertyListingAuthoringHttpController::class)
            ->defaults('authoring_operation', PropertyListingAuthoringHttpOperation::Portfolio->value);
    });

Route::prefix('/api/authoring/properties/{propertyId}/media')
    ->where(['propertyId' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}'])
    ->middleware([RequireIdentityAccessSession::class, 'throttle:property-listing-authoring'])
    ->group(function (): void {
        Route::post('/', MediaAuthoringHttpController::class)->defaults('media_authoring_operation', MediaAuthoringHttpOperation::Upload->value);
        Route::get('/', MediaAuthoringHttpController::class)->defaults('media_authoring_operation', MediaAuthoringHttpOperation::Collection->value);
        Route::delete('/{mediaId}', MediaAuthoringHttpController::class)
            ->where('mediaId', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}')
            ->defaults('media_authoring_operation', MediaAuthoringHttpOperation::Archive->value);
    });

Route::post('/api/public-authoring/v1/{journeyOperation}', PublicAuthoringJourneyController::class)
    ->middleware([RequireIdentityAccessSession::class, 'throttle:public-authoring-v1'])
    ->where('journeyOperation', 'initiate-property|update-property|create-listing|update-draft|grant-delegation|revoke-delegation|submit-listing')
    ->name('public-authoring.journey');

Route::get('/authoring/workspace', AuthoringWorkspaceController::class)
    ->middleware(RequireIdentityAccessSession::class)
    ->name('public-authoring.workspace');
Route::get('/authoring/workspace/{listingId}', AuthoringWorkspaceController::class)
    ->middleware(RequireIdentityAccessSession::class)
    ->name('public-authoring.workspace.resume');

Route::prefix('/publication-review')
    ->middleware([RequireIdentityAccessSession::class, 'throttle:iam-authenticated'])
    ->group(function (): void {
        $queueItem = '[A-Za-z0-9._:-]{1,96}';
        Route::get('/', [PublicationReviewExperienceController::class, 'index'])->name('publication-review.index');
        Route::get('/{queueItemId}', [PublicationReviewExperienceController::class, 'show'])->where('queueItemId', $queueItem)->name('publication-review.show');
        Route::post('/{queueItemId}/claim', [PublicationReviewExperienceController::class, 'claim'])->where('queueItemId', $queueItem)->defaults('review_operation', 'claim')->name('publication-review.claim');
        Route::post('/{queueItemId}/begin', [PublicationReviewExperienceController::class, 'begin'])->where('queueItemId', $queueItem)->defaults('review_operation', 'begin')->name('publication-review.begin');
        Route::post('/{queueItemId}/approve', [PublicationReviewExperienceController::class, 'approve'])->where('queueItemId', $queueItem)->defaults('review_operation', 'approve')->name('publication-review.approve');
    });

Route::middleware([RequireIdentityAccessSession::class, 'throttle:iam-authenticated'])
    ->group(function (): void {
        Route::get('/professional/mandate', [ProfessionalProfileHttpController::class, 'mandate'])
            ->name('professional.mandate');
        Route::get('/professional/status', [ProfessionalProfileHttpController::class, 'status'])
            ->name('professional.status');
    });

Route::prefix('/api/moderation/v1')
    ->middleware([RequireIdentityAccessSession::class, 'throttle:moderation-http-v1'])
    ->group(function (): void {
        $uuid = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}';
        Route::post('/reports/{reportId}', ModerationHttpController::class)->where('reportId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::SubmitReport->value);
        Route::post('/reports/{reportId}/validation', ModerationHttpController::class)->where('reportId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::ValidateReport->value);
        Route::post('/cases/{caseId}/findings', ModerationHttpController::class)->where('caseId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::RecordFinding->value);
        Route::post('/cases/{caseId}/decisions', ModerationHttpController::class)->where('caseId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::IssueDecision->value);
        Route::post('/cases/{caseId}/closure', ModerationHttpController::class)->where('caseId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::CloseCase->value);
        Route::post('/queue/{queueItemId}/claim', ModerationHttpController::class)->where('queueItemId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::ClaimQueueItem->value);
        Route::get('/reports/{reportId}', ModerationHttpController::class)->where('reportId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::ReadOwnReport->value);
        Route::get('/queue', ModerationHttpController::class)
            ->defaults('moderation_operation', ModerationHttpOperation::ReadQueue->value);
        Route::get('/cases/{caseId}', ModerationHttpController::class)->where('caseId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::ReadCase->value);
        Route::get('/cases/{caseId}/decisions', ModerationHttpController::class)->where('caseId', $uuid)
            ->defaults('moderation_operation', ModerationHttpOperation::ReadDecision->value);
    });

Route::get('/{canonicalPath}', PublicListingController::class)
    ->where('canonicalPath', 'annonces/[^/]+')
    ->name('public-listing.show');
