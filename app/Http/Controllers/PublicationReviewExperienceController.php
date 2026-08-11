<?php

namespace App\Http\Controllers;

use App\Application\PublicationReviewExperience\Contract\PublicationReviewExperienceV1;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceResult;
use App\Application\PublicationReviewExperience\PublicationReviewExperienceStatus;
use App\Http\Requests\PublicationReviewExperienceRequest;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class PublicationReviewExperienceController extends Controller
{
    public function __construct(private readonly PublicationReviewExperienceV1 $experience) {}

    public function index(Request $request): View
    {
        return $this->render($this->experience->queue($this->account($request), new DateTimeImmutable), 'queue');
    }

    public function show(Request $request, string $queueItemId): View
    {
        return $this->render($this->experience->candidate($this->account($request), $queueItemId, new DateTimeImmutable), 'candidate');
    }

    public function claim(PublicationReviewExperienceRequest $request, string $queueItemId): View
    {
        $result = $this->experience->claim(
            $this->account($request),
            $queueItemId,
            (string) $request->validated('commandId'),
            (int) $request->validated('expectedVersion'),
            new DateTimeImmutable,
        );

        return $this->render($result, 'claimed');
    }

    public function begin(PublicationReviewExperienceRequest $request, string $queueItemId): View
    {
        $result = $this->experience->begin(
            $this->account($request),
            $queueItemId,
            (string) $request->validated('commandId'),
            (int) $request->validated('expectedVersion'),
            new DateTimeImmutable,
        );

        return $this->render($result, 'reviewing', [
            'queueItemId' => $queueItemId,
            'listingId' => (string) $request->validated('listingId'),
            'submissionVersion' => (int) $request->validated('submissionVersion'),
        ]);
    }

    public function approve(PublicationReviewExperienceRequest $request, string $queueItemId): View
    {
        $result = $this->experience->approve(
            $this->account($request),
            $queueItemId,
            (string) $request->validated('listingId'),
            (int) $request->validated('submissionVersion'),
            (string) $request->validated('commandId'),
            (string) $request->validated('projectionCommandId'),
            (int) $request->validated('expectedVersion'),
            new DateTimeImmutable,
        );

        return $this->render($result, 'confirmed');
    }

    /** @param array<string, mixed> $context */
    private function render(PublicationReviewExperienceResult $result, string $mode, array $context = []): View
    {
        match ($result->status) {
            PublicationReviewExperienceStatus::Forbidden => throw new AccessDeniedHttpException,
            PublicationReviewExperienceStatus::NotFound => throw new NotFoundHttpException,
            PublicationReviewExperienceStatus::Conflict => throw new ConflictHttpException,
            PublicationReviewExperienceStatus::DependencyUnavailable => throw new ServiceUnavailableHttpException,
            default => null,
        };

        return view('publication-review', ['mode' => $mode, 'result' => $result] + $context);
    }

    private function account(Request $request): AccountId
    {
        $account = $request->attributes->get('iam_account_id');
        if (! $account instanceof AccountId) {
            throw new AccessDeniedHttpException;
        }

        return $account;
    }
}
