# Final mutation transport

Enabled/Disabled/Merged réutilisent le transport lifecycle existant. `PlaceRenamed` Domain est admis avec payload V1 dans la même outbox atomique Geography, en conservant eventId déterministe, PlaceId, aggregateVersion, occurredAt et causalité.

Le consumer lance lookup et refresh terminal. Retry/quarantine/replay réutilisent l'infrastructure existante.
