# Preuves d’atomicité transactionnelle

## Promotion F6

Property et ledger Promotion sont écrits dans une transaction locale. Une défaillance lors de l’écriture ledger retourne `DependencyUnavailable` et rollback la Property : zéro Property et zéro ledger.

## Listing F7-A

Une défaillance fermée après application apparente rollback simultanément :

- Aggregate Listing ;
- Workflow ;
- public facts ;
- outbox et delivery ;
- item PublicationReview.

La Property et le ledger F6, déjà engagés dans leur propre autorité transactionnelle, restent durables.
