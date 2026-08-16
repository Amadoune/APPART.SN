# F7-B — Preuve Property existante

## Cas canonique sans ledger correspondant

Une Property réellement persistée avec la version initiale, les faits attendus et l’AddressId canonique est relue alors que le nouveau commandId ne possède aucune entrée ledger.

Résultat observé : `AlreadyApplied`.

Garanties observées :

- une seule Property demeure persistée ;
- aucun ledger de rattrapage n’est créé ;
- aucune mutation de l’Aggregate n’est effectuée.

## Cas divergents

Les divergences unitaires de référence, type, surface, pièces, salles de bain, année de construction et version produisent toutes `DivergentCommand`. Les divergences d’AddressId, PlaceId, AddressLine et de présence d’adresse produisent le même résultat fermé.

Le cas PostgreSQL AddressId divergent confirme une Property unique, inchangée, et zéro ledger de succès.

## Ledger

Le replay attesté reste prioritaire et retourne `AlreadyApplied`. Un commandId réutilisé avec checksum divergent reste refusé par `DivergentCommand` sans requalification de l’Aggregate courant.
