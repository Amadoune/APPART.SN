<?php

namespace App\Application\MediaIngestionEventIntegration;

use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxWriter;
use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxWriteResult;
use App\Application\MediaIngestionEventRouting\DeterministicMediaIngestionEventRouter;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;
use Appart\Modules\Media\Application\MediaIngestionEventIntegration\Contract\MediaIngestionAtomicDeliveryTransaction;
use RuntimeException;

final readonly class MediaIngestionAtomicDelivery
{
    public function __construct(
        private MediaIngestionAtomicDeliveryTransaction $transaction,
        private DeterministicMediaIngestionEventRouter $router,
        private MediaIngestionOutboxWriter $outbox,
    ) {}

    /**
     * @param  callable(): MediaIngestionAtomicDeliveryResult  $work
     * @param  list<MediaIngestionEventV1>  $events
     */
    public function execute(callable $work, array $events): MediaIngestionAtomicDeliveryResult
    {
        try {
            return $this->transaction->execute(function () use ($work, $events): MediaIngestionAtomicDeliveryResult {
                $result = $work();
                if ($result === MediaIngestionAtomicDeliveryResult::Rejected) {
                    throw new MediaIngestionAtomicDeliveryRejected;
                }

                foreach ($events as $event) {
                    $message = new MediaIngestionDeliveryMessageV1($event);
                    $routing = $this->router->route($message);
                    if ($routing->destinations === []) {
                        throw new RuntimeException('Media Ingestion event routing was rejected.');
                    }
                    $written = $this->outbox->append($message, $routing->destinations);
                    if ($written === MediaIngestionOutboxWriteResult::DivergentMessage) {
                        throw new RuntimeException('Media Ingestion Outbox rejected a divergent event.');
                    }
                }

                return $result;
            });
        } catch (MediaIngestionAtomicDeliveryRejected) {
            return MediaIngestionAtomicDeliveryResult::Rejected;
        }
    }
}
