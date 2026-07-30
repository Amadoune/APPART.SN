# Professional Status HTTP Contract

| Champ | Règle |
|---|---|
| `professionalId` | UUID de route obligatoire |
| `action` | `suspend` ou `reactivate` |
| `expectedVersion` | entier strictement positif |
| `actorId` | UUID explicite |
| `occurredAt` | UTC canonique avec six microsecondes |
| `recordedAt` | UTC canonique avec six microsecondes, supérieur ou égal à `occurredAt` |

Chaque valeur est transmise sans génération implicite à `ProfessionalStatusAtomicEventRequest`. Aucune horloge, identité ou version n'est créée par HTTP. Le corps JSON de sortie est limité à `status` et `diagnostic` et n'expose aucune information technique interne.
