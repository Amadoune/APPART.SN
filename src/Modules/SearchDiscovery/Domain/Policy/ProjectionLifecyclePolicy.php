<?php

namespace Appart\Modules\SearchDiscovery\Domain\Policy;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionChange;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;

final readonly class ProjectionLifecyclePolicy
{
    public function decide(?ProjectionState $current, ProjectionState $next): ProjectionChange
    {
        if ($current === null) {
            return ProjectionChange::Indexed;
        }
        if ($current === ProjectionState::Removed && $next !== ProjectionState::Removed) {
            return ProjectionChange::Rebuilt;
        }
        if ($next === ProjectionState::Removed) {
            return ProjectionChange::Removed;
        }
        if ($next === ProjectionState::Hidden) {
            return ProjectionChange::Hidden;
        }

        return ProjectionChange::Reindexed;
    }
}
