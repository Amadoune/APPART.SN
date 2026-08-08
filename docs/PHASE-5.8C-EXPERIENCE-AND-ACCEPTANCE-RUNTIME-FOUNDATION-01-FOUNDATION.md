# Phase 5.8C — Experience & Acceptance — Runtime Foundation

## Périmètre

Le Runtime technique ExperienceAcceptance dépend exclusivement de `ExperienceAcceptanceOwnerSource`. Il expose uniquement la disponibilité technique de la source owner-scoped.

Composants : Runtime V1, Availability, AvailabilityPolicy, Diagnostics et leurs implémentations déterministes, plus un Service Provider dédié.

Aucun Runtime Read, Owner Reader, HTTP, Event, Delivery, Outbox, Transport, Routing ou Consumer n'est ouvert.

