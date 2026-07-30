<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

use RuntimeException;

final class UnsupportedReservationLifecycleEventTransition extends RuntimeException {}
