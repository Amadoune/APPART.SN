<?php

namespace App\Http\Controllers;

use App\Application\Contract\PublicListingQuery;
use App\Http\HistoricalRedirectHttpDiagnosticCode;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationStatus;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectStatus;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Illuminate\Contracts\View\View;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

final class PublicListingController extends Controller
{
    public function __construct(
        private readonly PublicListingQuery $listings,
        private readonly HistoricalCanonicalQualifier $qualifier,
        private readonly HistoricalRedirectResolver $redirects,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(string $canonicalPath): View|RedirectResponse
    {
        try {
            $listing = $this->listings->findByCanonicalPath($canonicalPath);
        } catch (Throwable $error) {
            throw new ServiceUnavailableHttpException(null, 'Public projection is temporarily unavailable.', $error);
        }

        if ($listing !== null) {
            return view('public-listing', ['listing' => $listing]);
        }

        try {
            $qualification = $this->qualifier->qualify(CanonicalUrl::fromString('https://appart.sn/'.$canonicalPath));
        } catch (Throwable $error) {
            $this->report(HistoricalRedirectHttpDiagnosticCode::QualificationUnavailable);

            throw new ServiceUnavailableHttpException(null, 'Historical canonical qualification is temporarily unavailable.', $error);
        }

        return $this->qualificationResponse($qualification);
    }

    private function qualificationResponse(HistoricalCanonicalQualification $qualification): RedirectResponse
    {
        return match ($qualification->status) {
            HistoricalCanonicalQualificationStatus::Current => $this->notFound(HistoricalRedirectHttpDiagnosticCode::QualificationCurrent),
            HistoricalCanonicalQualificationStatus::Unknown => $this->notFound(HistoricalRedirectHttpDiagnosticCode::QualificationUnknown),
            HistoricalCanonicalQualificationStatus::Ambiguous => $this->unavailable(HistoricalRedirectHttpDiagnosticCode::QualificationAmbiguous),
            HistoricalCanonicalQualificationStatus::Corrupted => $this->unavailable(HistoricalRedirectHttpDiagnosticCode::QualificationCorrupted),
            HistoricalCanonicalQualificationStatus::Historical => $this->resolveHistorical($qualification),
        };
    }

    private function resolveHistorical(HistoricalCanonicalQualification $qualification): RedirectResponse
    {
        if ($qualification->historicalCanonical === null) {
            return $this->unavailable(HistoricalRedirectHttpDiagnosticCode::QualificationCorrupted);
        }

        try {
            $resolution = $this->redirects->resolve($qualification->historicalCanonical);
        } catch (Throwable $error) {
            $this->report(HistoricalRedirectHttpDiagnosticCode::ResolverUnavailable);

            throw new ServiceUnavailableHttpException(null, 'Historical redirect resolution is temporarily unavailable.', $error);
        }

        return $this->redirectResponse($resolution);
    }

    private function redirectResponse(HistoricalRedirectResolution $resolution): RedirectResponse
    {
        return match ($resolution->status) {
            HistoricalRedirectStatus::Resolved => new RedirectResponse($resolution->target?->canonical->value ?? throw new ServiceUnavailableHttpException, 301),
            HistoricalRedirectStatus::NotFound => $this->notFound(HistoricalRedirectHttpDiagnosticCode::ResolverNotFound),
            HistoricalRedirectStatus::DestinationMissing => $this->notFound(HistoricalRedirectHttpDiagnosticCode::DestinationMissing),
            HistoricalRedirectStatus::LoopDetected => $this->unavailable(HistoricalRedirectHttpDiagnosticCode::LoopDetected),
            HistoricalRedirectStatus::ChainDetected => $this->unavailable(HistoricalRedirectHttpDiagnosticCode::ChainDetected),
            HistoricalRedirectStatus::Ambiguous => $this->unavailable(HistoricalRedirectHttpDiagnosticCode::ResolverAmbiguous),
            HistoricalRedirectStatus::Corrupted => $this->unavailable(HistoricalRedirectHttpDiagnosticCode::ResolverCorrupted),
        };
    }

    private function notFound(HistoricalRedirectHttpDiagnosticCode $code): never
    {
        $this->report($code);

        throw new NotFoundHttpException;
    }

    private function unavailable(HistoricalRedirectHttpDiagnosticCode $code): never
    {
        $this->report($code);

        throw new ServiceUnavailableHttpException;
    }

    private function report(HistoricalRedirectHttpDiagnosticCode $code): void
    {
        $this->logger->warning('historical_redirect_http_outcome', ['diagnostic_code' => $code->value]);
    }
}
