# Final Decision Time Model

`decisionAt` est exactement `PublishedPublicFacts::publishedAt`, owner ListingLifecycle, type `DateTimeImmutable` et précision microseconde.

Pour RC2 : `2026-08-15T09:22:24.151935+02:00`.

Handoff, catch-up et replay du même Published réutilisent cet instant. Le snapshot le persiste et `ContentSeoSnapshotDecisionTimeReader` le retourne sans nouvelle décision ni `now()`.
