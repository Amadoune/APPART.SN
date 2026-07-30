# Phase 4.7B-R1 — Historical Coexistence Read & Write Specification

## Avant enrôlement

Le registre historique demeure seul disponible. Une lecture historique peut produire un checkpoint contenant identité, version historique, état exact et checksum SHA-256 de la source. Elle ne constitue pas encore une lecture Lifecycle.

## Enrôlement

Le checkpoint est appendé une fois dans le futur journal. Un rejeu strictement identique est idempotent. Un checkpoint divergent est refusé. Une source absente, indisponible ou corrompue reste un résultat distinct.

## Après enrôlement

- toute lecture Lifecycle utilise exclusivement le journal ;
- aucune absence ou corruption du journal ne déclenche une lecture historique de secours ;
- les consommateurs historiques continuent à lire le registre historique ;
- une transition Lifecycle append le journal et met à jour le miroir historique dans la même transaction ;
- un échec de l'une des écritures annule les deux.

Le statut historique devient un miroir de compatibilité pour les actions enrôlées, jamais une seconde source de décision Lifecycle.
