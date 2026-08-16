# Preuves d’implémentation

## Application

- contrat, commande, résultat et statuts sous `RealEstateCatalog/Application/Promotion` ;
- orchestrateur `DeterministicPromoteAuthoredPropertyV1` ;
- assemblage exclusif des Value Objects existants ;
- appels réels aux autorités F2 et F3 puis à `RegisterProperty`.

## Infrastructure

- ledger PostgreSQL productif ;
- transaction locale et participant transactionnel du registre ;
- binding singleton explicite dans `PublicPropertyPromotionServiceProvider` ;
- migration additive 100 et rollback dédié.

## Composition

- binding injecté dans `PropertyListingAuthoringOperationsServiceProvider` ;
- Submit transporte uniquement `expectedAuthoringVersion` en plus de son identité existante ;
- le `propertyId` est dérivé du Listing Draft, l’owner de la session IAM.

## Invariants observés

Les tests démontrent owner mismatch fermé, version conflict, snapshot incomplet, Geography négative, checksum/replay, rollback du ledger, unicité Property et blocage du Submit avant `Submitted`.
