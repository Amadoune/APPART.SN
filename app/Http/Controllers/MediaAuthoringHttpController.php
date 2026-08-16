<?php

namespace App\Http\Controllers;

use App\Application\MediaAuthoringHttp\Contract\MediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpOperation;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpResult;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpStatus;
use App\Http\Requests\MediaAuthoringHttpRequest;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class MediaAuthoringHttpController extends Controller
{
    public function __construct(private readonly MediaAuthoringHttpRuntime $runtime) {}

    public function __invoke(MediaAuthoringHttpRequest $request): JsonResponse
    {
        $account = $request->attributes->get('iam_account_id');
        $propertyId = $request->route('propertyId');
        if (! $account instanceof AccountId || ! is_string($propertyId)) {
            return $this->respond(new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::NotFoundOrForbidden));
        }
        try {
            $result = match ($request->operation()) {
                MediaAuthoringHttpOperation::Upload => $this->upload($request, $account->value, $propertyId),
                MediaAuthoringHttpOperation::Collection => $this->runtime->collection($account->value, $propertyId),
                MediaAuthoringHttpOperation::Archive => $this->runtime->archive(
                    $account->value,
                    $propertyId,
                    (string) $request->route('mediaId'),
                    $request->validated('replacementMediaId'),
                    (new DateTimeImmutable)->format(DATE_ATOM),
                ),
            };

            return $this->respond($result);
        } catch (Throwable $error) {
            Log::error('media_authoring.http.unavailable', [
                'stage' => 'uncaught_exception',
                'exception' => $error::class,
                'source' => basename($error->getFile()),
                'line' => $error->getLine(),
            ]);

            return $this->respond(new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Unavailable));
        }
    }

    private function upload(MediaAuthoringHttpRequest $request, string $ownerAccountId, string $propertyId): MediaAuthoringHttpResult
    {
        $image = $request->file('image');
        if ($image === null || ! $image->isValid()) {
            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Invalid);
        }
        $stream = fopen($image->getPathname(), 'rb');
        if (! is_resource($stream)) {
            Log::error('media_authoring.http.unavailable', [
                'stage' => 'open_stream',
                'upload_error' => $image->getError(),
                'readable' => is_readable($image->getPathname()),
            ]);

            return new MediaAuthoringHttpResult(MediaAuthoringHttpStatus::Unavailable);
        }
        try {
            $result = $this->runtime->upload(
                $ownerAccountId,
                $propertyId,
                (string) $request->validated('_intentId'),
                $image->getClientOriginalName(),
                $image->getMimeType() ?? 'application/octet-stream',
                $stream,
                (int) $request->validated('order'),
                $request->validated('caption'),
                (new DateTimeImmutable)->format(DATE_ATOM),
            );
            if ($result->status === MediaAuthoringHttpStatus::Unavailable) {
                Log::error('media_authoring.http.unavailable', ['stage' => 'runtime_unavailable']);
            }

            return $result;
        } finally {
            fclose($stream);
        }
    }

    private function respond(MediaAuthoringHttpResult $result): JsonResponse
    {
        $code = match ($result->status) {
            MediaAuthoringHttpStatus::Created => 201,
            MediaAuthoringHttpStatus::Available,
            MediaAuthoringHttpStatus::Empty => 200,
            MediaAuthoringHttpStatus::NotFoundOrForbidden => 404,
            MediaAuthoringHttpStatus::Invalid => 422,
            MediaAuthoringHttpStatus::Conflict => 409,
            MediaAuthoringHttpStatus::Unavailable => 503,
        };

        return new JsonResponse(['status' => $result->status->value] + $result->data, $code, [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
