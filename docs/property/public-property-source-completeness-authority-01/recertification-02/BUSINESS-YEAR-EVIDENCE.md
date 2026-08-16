# Business Year Evidence

F3 transforme l’instant stable de commande :

`PropertyDecisionOccurredAt` → `UtcCalendarBusinessYearAuthorityV1` → `BusinessYearResolutionResult::Resolved` → `BusinessYear`.

La résolution convertit l’instant absolu en UTC et extrait son année. Elle ne lit aucune clock et Authoring ne stocke aucun BusinessYear.

Les tests couvrent UTC, offset équivalent, replay, instant sans timezone et date invalide. La preuve terminale résout l’instant `2026-08-14T10:00:00.000000Z` en `BusinessYear(2026)`.
