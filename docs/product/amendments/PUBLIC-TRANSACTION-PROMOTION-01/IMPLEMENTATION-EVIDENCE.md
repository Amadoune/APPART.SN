# Public Transaction Promotion 01 — Implementation Evidence

## Statut

`NOT_IMPLEMENTED`.

## Cause racine

La source Authoring possède `transactionKind`, mais aucun command, Aggregate, événement ou snapshot de Listing Lifecycle ne la transporte jusqu'à la publication. La projection ne peut donc pas la promouvoir mécaniquement.

## Changements requis mais non autorisés

- contrat de handoff Authoring → Listing Lifecycle ;
- représentation owner-scoped de la transaction dans le Listing publié ;
- compatibilité de persistance pour les Listings existants ;
- reconstruction contrôlée des projections publiques ;
- traitement explicite de la donnée P02 historique.

Ces éléments dépassent la simple extension du read model et exigent une décision d'autorité distincte.

## Garanties conservées

- aucune relecture d'Authoring ;
- aucune jointure vers `ListingDraftStore` ;
- aucune valeur Acheter/Louer déduite ou inventée ;
- aucune projection, persistence ou moteur parallèle ;
- aucun changement P02, R5 ou Phase 5.10.
