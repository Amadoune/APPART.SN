# Media Item Lifecycle Context V1 Specification

## Structure canonique

| Champ | Type | Invariant |
|---|---|---|
| `contractVersion` | `MediaItemLifecycleContextVersion` | exactement `V1` |
| `collectionId` | `MediaCollectionId` | identité explicite |
| `mediaId` | `MediaId` | média soumis à la transition |
| `expectedVersion` | `MediaItemLifecycleExpectedVersion` | entier strictement positif |
| `collectionVersion` | `MediaCollectionDecisionVersion` | entier positif ou nul, fourni par la collection |
| `actor` | `MediaItemLifecycleActorId` | UUID explicite |
| `occurredAt` | `MediaItemLifecycleOccurredAt` | UTC explicite, précision microseconde |
| `collectionDecision` | `MediaCollectionTransitionDecision` | décision fermée déjà produite |

Tous les objets sont finaux, immuables et dépourvus de valeur par défaut.

## Canonicalisation

Le checksum est le SHA-256 hexadécimal minuscule de la concaténation, séparée par LF, des champs dans l'ordre du tableau, suivis de :

1. la disposition de la décision ;
2. l'identité du remplaçant, ou la chaîne vide pour `NotPrimary`.

Aucune horloge, identité, version ou normalisation locale n'est implicite.
