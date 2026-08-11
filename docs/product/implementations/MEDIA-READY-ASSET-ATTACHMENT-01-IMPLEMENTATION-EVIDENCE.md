# MEDIA READY ASSET ATTACHMENT 01 — IMPLEMENTATION EVIDENCE

## Composants

- `DeterministicAttachReadyMediaAsset` implémente le contrat existant `AttachReadyMediaAssetV1`.
- `MediaAttachmentTransaction` borne l'atomicité applicative.
- `PostgreSqlMediaAttachmentTransaction` conserve transaction externe et savepoints.
- `PostgreSqlMediaAttachmentIntentStore` réutilise exclusivement le journal 059 existant.
- `RegistryMediaPropertyCatalog` adapte mécaniquement le registre Property existant.
- `MediaReadyAssetAttachmentServiceProvider` expose un singleton et un alias nominatif.
- `MediaIngestionRuntimeV1::attachment()` expose la capacité dans le Runtime Media existant.

## Garanties démontrées

| Garantie | Preuve |
|---|---|
| READY obligatoire | un asset `quarantined` est refusé sans collection créée |
| attachement réel | le média est relu dans la collection PostgreSQL |
| collection réelle et unique | une ligne collection après application et replay |
| idempotence | replay exact `AlreadyApplied` |
| optimistic locking | expected collection version et sauvegarde conditionnelle du registre |
| owner-scoped | collection liée immuablement à la Property et identité Media réservée globalement |
| aucun doublon | une ligne intent, une collection, un item après replay |
| transaction | intent, collection et item participent à une transaction locale unique |
| aucune façade | aucune route, Request ou Controller ajouté |

Les migrations historiques, dont 059, sont inchangées.
