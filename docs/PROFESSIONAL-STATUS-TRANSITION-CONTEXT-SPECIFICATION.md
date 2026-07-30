# Professional Status Transition Context Specification

Le contexte immuable contient exactement :

| Champ | Type | Invariant |
|---|---|---|
| `actor` | `ProfessionalStatusActorId` | UUID explicite |
| `occurredAt` | `ProfessionalStatusOccurredAt` | UTC explicite, aucune horloge |
| `expectedVersion` | `ProfessionalStatusExpectedVersion` | entier strictement positif |

La version du futur append est exclusivement `expectedVersion + 1`. Aucun acteur, instant ou numéro de version par défaut n'est autorisé.

Le checksum contextuel est un SHA-256 typé. Sa formule canonique sera figée avec la future persistance contextuelle ; aucune infrastructure n'est autorisée à inventer une valeur.
