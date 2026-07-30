# Place Merge Context V1 Specification

## Objet

`Place Merge Context V1` est la preuve immuable préparée avant une future
décision de fusion. Il ne décide rien, ne lit rien et ne persiste rien.

## Données explicites

| Champ | Type conceptuel | Règle |
|---|---|---|
| version de contrat | `V1` | constante et explicite |
| source | `PlaceId` | identité propriétaire |
| cible | `PlaceId` | identité observée |
| version source attendue | entier positif | concurrence source |
| version cible observée | entier positif | stabilité de la preuve |
| état cible observé | `Enabled`, `Disabled`, `Merged` | aucune reconstruction |
| type source observé | `PlaceType` | comparaison future explicite |
| type cible observé | `PlaceType` | aucune résolution future |
| pays source observé | `CountryCode` | comparaison future explicite |
| pays cible observé | `CountryCode` | aucune résolution future |
| acteur | UUID explicite | aucune identité implicite |
| instant métier | UTC explicite | aucune horloge implicite |
| intention | UUID explicite | identité d'idempotence |

Le contexte accepte les preuves métier défavorables : source égale à la cible,
cible désactivée ou fusionnée, types ou pays divergents. Ces situations doivent
rester disponibles au futur Workflow afin qu'il produise un résultat fermé.
Le constructeur ne porte que les invariants intrinsèques des valeurs.

## Immutabilité

Le contexte et tous ses Value Objects locaux sont `readonly`. Aucun setter,
provider, callback, service ou accès externe n'est admis.

## Inspection et rejeu

`PlaceMergeContextInspector` définit uniquement la forme d'une inspection par
source et intention. Il ne possède aucune implémentation dans ce sprint.

Résultats d'inspection fermés :

- `Found` avec inspection exacte ;
- `Missing` ;
- `Corrupted`.

L'inspection exacte porte le contexte V1 et la version source résultante,
obligatoirement égale à la version attendue plus un.

`PlaceMergeReplayClassifier` définit uniquement le contrat de classement. Les
issues de rejeu sont fermées :

- `AlreadyApplied` ;
- `ContextDivergence` ;
- `Conflict` ;
- `InspectionMissing` ;
- `InspectionCorrupted`.

Aucune politique concrète, inspection durable ou stratégie de persistance
n'est créée par 4.8A-R1.

## Interdictions

Le contrat ne dépend d'aucun Workflow, Aggregate, Repository, Runtime,
framework, Event, Transport, Routing, Outbox, HTTP, base de données ou
transaction. Il ne modifie aucun contrat certifié.
