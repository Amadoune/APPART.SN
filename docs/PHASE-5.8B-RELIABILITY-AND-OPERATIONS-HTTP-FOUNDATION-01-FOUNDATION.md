# Phase 5.8B — Reliability & Operations — HTTP Foundation

La façade HTTP expose exclusivement sept endpoints GET consommant les sept Readers V1 certifiés. Chaque Controller dépend de son Reader V1 et de `ReliabilityOperationsResponseFactory`.

Les Requests exigent uniquement `observedAt` au format canonique accepté et refusent tout champ inconnu. Les réponses exposent seulement `status` et `observedAt`, avec `Cache-Control: no-store` et `X-Content-Type-Options: nosniff`.

Aucun accès OwnerSource, Runtime, Persistence ou Infrastructure n'est autorisé.
