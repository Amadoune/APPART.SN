# F3 — Certification Note

F3 matérialise exclusivement l'activation idempotente de la Projection publique après publication terminale.

Les preuves établissent :

- Projection réelle par le `PublicListingProjectionUpdater` certifié ;
- `Applied`, `AlreadyApplied`, `NotReady`, `Conflict` et `DependencyUnavailable` sans fallback ;
- replay arrêté par le ledger avant toute nouvelle activation ;
- QueueItem terminal conservé et versionné ;
- absence de SQL dans Application ;
- absence de règle métier et d'écriture Search directe ;
- migrations 096–097 inchangées.

Aucune UI et aucun chantier P08 ne sont ouverts.

## Verdict proposé

**GO PROPOSÉ — APPART.SN PUBLICATION REVIEW FOUNDATION v1 — F3 PROJECTION ACTIVATION**
