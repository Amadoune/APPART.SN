<?php

namespace Appart\Modules\ContentSeo\Application\Outbox;

enum ContentSeoOutboxStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
