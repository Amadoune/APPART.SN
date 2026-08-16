# F7 — Public Property Promotion Recertification — Reopening 01

## Historique préservé

- F7 Recertification 01 : **NO GO historique**, rollback Listing non garanti.
- F7-A Authority : **GO**, outcome transactionnel défini.
- F7-A Implementation : **GO**, correction et preuves exécutées.

## Reprise ordonnée

Le scénario historiquement bloquant est fermé par la preuve PostgreSQL F7-A : closed failure, rollback complet Listing/handoffs, Property et ledger Promotion durables, retry sans doublon jusqu’à Submitted.

Les preuves Applied, replay identique et commande divergente restent disponibles dans F6. La reprise atteint ensuite l’étape 7, Aggregate Property déjà existant.

## Nouvelle première divergence

`DeterministicPromoteAuthoredPropertyV1::compatible()` compare l’adresse existante par `Address::equals()`. Cette méthode compare `placeId` et `line`, mais pas `AddressId`.

Un Aggregate avec AddressId non canonique mais même Place/Line est donc déclaré compatible et retourne `AlreadyApplied`. Il devrait être divergent au regard du mapping canonique F2 exigé par F7.

Audit arrêté fail-fast. Aucun code ou test ajouté.
