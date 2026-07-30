<?php

namespace App\Application\PublicProjectionOutbox\Contract;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursor;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursorIdentity;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxReplayRequest;

interface PublicProjectionOutboxCursorStore
{
    public function find(PublicProjectionOutboxCursorIdentity $identity): ?PublicProjectionOutboxCursor;

    public function advance(PublicProjectionOutboxCursor $cursor): void;

    public function beginReplay(PublicProjectionOutboxReplayRequest $request): void;
}
