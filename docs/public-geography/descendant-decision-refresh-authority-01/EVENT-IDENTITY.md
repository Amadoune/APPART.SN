# Event identity

L'intention conserve source eventId, mutatedPlaceId, aggregateVersion, occurredAt et causation/correlation disponibles. Rename reçoit une identité déterministe selon les conventions Geography existantes, pas un UUID runtime.

Même event → même intention de refresh.
