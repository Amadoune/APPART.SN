<?php

namespace App\Http\Controllers;

use App\Application\AuthoringDraftResume\AuthoringDraftResumeStatus;
use App\Application\AuthoringDraftResume\Contract\AuthoringDraftResumeReaderV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AuthoringWorkspaceController extends Controller
{
    public function __construct(private readonly AuthoringDraftResumeReaderV1 $resume) {}

    public function __invoke(Request $request, ?string $listingId = null): View|Response
    {
        if ($listingId === null) {
            return view('authoring-workspace', ['resumeSnapshot' => null, 'resumeError' => null]);
        }
        if (! Str::isUuid($listingId)) {
            return response()->view('authoring-workspace', ['resumeSnapshot' => null, 'resumeError' => 'invalid'], 422);
        }
        $account = $request->attributes->get('iam_account_id');
        if (! $account instanceof AccountId) {
            return response()->view('authoring-workspace', ['resumeSnapshot' => null, 'resumeError' => 'authentication_required'], 401);
        }

        $result = $this->resume->read($account->value, strtolower($listingId));
        if ($result->status === AuthoringDraftResumeStatus::Available) {
            return view('authoring-workspace', ['resumeSnapshot' => $result->snapshot, 'resumeError' => null]);
        }

        $status = match ($result->status) {
            AuthoringDraftResumeStatus::NotFoundOrForbidden => 404,
            AuthoringDraftResumeStatus::Incomplete,
            AuthoringDraftResumeStatus::StateConflict => 409,
            AuthoringDraftResumeStatus::Corrupted => 500,
            AuthoringDraftResumeStatus::DependencyUnavailable => 503,
        };

        return response()->view('authoring-workspace', [
            'resumeSnapshot' => null,
            'resumeError' => $result->status->value,
        ], $status);
    }
}
