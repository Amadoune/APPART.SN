# Architecture Overview

APPART.SN est un monolithe modulaire. Chaque module possède son Domain autonome et son Application ; les ports sont exprimés localement par le consommateur. Le Domain ne dépend ni de Laravel, ni d’Infrastructure, ni d’un autre module. Les interactions futures passent par contrats publics, identités stables et événements.

## Modules

AdministrationAudit, ContactsLeads, ContentSeo, Geography, IdentityAccess, LegacyMigration, ListingLifecycle, Media, ModerationReports, MonetizationPayments, Professionals, RealEstateCatalog et SearchDiscovery. Douze sont implémentés ; LegacyMigration reste une enveloppe vide.

## Dépendances

`Application/UseCase → Application/Contract + Domain`. Les futurs adapters Infrastructure pourront satisfaire les ports, sans remonter dans Domain ni être appelés directement par Application. SearchDiscovery et ContentSeo sont des projections reconstruisibles, jamais des sources de vérité des modules producteurs.

## Persistance cible

PostgreSQL 18.x, optimistic locking par version, mapping explicite par Aggregate, Unit of Work courte, réservations et Outbox atomiques, Dispatcher asynchrone au moins une fois. À la baseline Sprint 2.2, ces éléments sont normatifs mais non implémentés.

Voir `MODULE-INVENTORY.md`, `AGGREGATE-MATRIX.md`, `BOUNDARY-AUDIT.md` et les huit documents de `docs/infrastructure`.
