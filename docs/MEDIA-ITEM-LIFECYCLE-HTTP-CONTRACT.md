# Media Item Lifecycle HTTP Contract

## Entrée

- `mediaId` : UUID de route ;
- `action` : `remove` ou `archive` ;
- `contextVersion` : exactement 1 ;
- `collectionId` : UUID ;
- `expectedVersion` : entier strictement positif ;
- `collectionVersion` : entier strictement positif ;
- `actorId` : UUID ;
- `occurredAt` et `recordedAt` : UTC avec six décimales ;
- `collectionDecision` : `not_primary` ou `replacement_selected` ;
- `replacementMediaId` : interdit pour `not_primary`, obligatoire et différent du média courant pour `replacement_selected`.

Le contrôleur ne génère aucune identité, version ou horloge.
