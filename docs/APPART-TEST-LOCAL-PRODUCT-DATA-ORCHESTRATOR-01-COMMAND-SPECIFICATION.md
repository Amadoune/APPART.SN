# APPART.TEST LOCAL PRODUCT DATA ORCHESTRATOR 01 — Command Specification

## Commandes candidates

- `appart:local:create-public-listing`
- `appart:local:cleanup-product-data`

## Statut

`NOT_IMPLEMENTED`

Créer des commandes qui échoueraient nécessairement au premier bootstrap de génération ne fournirait pas l'outil certifiable demandé. Aucun squelette exécutable n'a donc été ajouté.

## Préconditions obligatoires qualifiées

- environnement Laravel strictement `local` ;
- base strictement `appart_test` ;
- confirmation interactive ou `--force-dev` ;
- marqueur de développement stable ;
- arrêt sur tout résultat typé inattendu ;
- aucune disponibilité en production.

## Amendement minimal requis

Une décision distincte doit qualifier une primitive applicative de première génération, sans SQL ad hoc, puis une composition certifiée maintenant synchronisés le workflow de publication et le `ListingRegistry`. Cette note n'ouvre ni ne conçoit leur implémentation.
