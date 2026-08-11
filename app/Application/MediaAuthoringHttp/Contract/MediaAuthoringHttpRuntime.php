<?php

namespace App\Application\MediaAuthoringHttp\Contract;

use App\Application\MediaAuthoringHttp\MediaAuthoringHttpResult;

interface MediaAuthoringHttpRuntime
{
    public function upload(string $ownerAccountId, string $propertyId, string $intentId, string $originalName, string $contentType, mixed $stream, int $order, ?string $caption, string $occurredAt): MediaAuthoringHttpResult;

    public function collection(string $ownerAccountId, string $propertyId): MediaAuthoringHttpResult;

    public function archive(string $ownerAccountId, string $propertyId, string $mediaId, ?string $replacementMediaId, string $occurredAt): MediaAuthoringHttpResult;
}
