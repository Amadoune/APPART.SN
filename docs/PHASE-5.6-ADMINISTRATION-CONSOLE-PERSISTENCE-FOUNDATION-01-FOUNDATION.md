# Administration Console Persistence Foundation

Jalon `PHASE-5.6-ADMINISTRATION-CONSOLE-PERSISTENCE-FOUNDATION-01` — GO CERTIFIÉ — OUVERTE.

`AdministrationConsole` est l'owner unique. `AdministrationConsoleOwnerSource` expose trois streams indépendants : Operator, Queue et Audit. Le journal append-only est l'autorité durable ; `owner_current_index` est une vue dérivée, non autoritative.

La lecture temporelle exige que `effective_at` et `recorded_at` soient antérieurs ou égaux à l'instant observé. Les écritures sont owner-locales et ne reproduisent aucune décision IAM, Moderation, Notifications, ContentSeo ou AdministrationAudit.
