<?php

namespace Tests\Unit\Contracts\PublicProjectionOutbox\Support;

use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxCursorStore;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursor;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursorIdentity;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxReplayRequest;

final class FakePublicProjectionOutboxCursorStore implements PublicProjectionOutboxCursorStore
{
    /** @var array<string, PublicProjectionOutboxCursor> */
    private array $cursors = [];

    /** @var list<PublicProjectionOutboxReplayRequest> */
    public array $replays = [];

    public function find(PublicProjectionOutboxCursorIdentity $identity): ?PublicProjectionOutboxCursor
    {
        return $this->cursors[$identity->value()] ?? null;
    }

    public function advance(PublicProjectionOutboxCursor $cursor): void
    {
        $this->cursors[$cursor->identity->value()] = $cursor;
    }

    public function beginReplay(PublicProjectionOutboxReplayRequest $request): void
    {
        $this->replays[] = $request;
    }
}
