# Reader Contract

## GeographySelectionReaderV1

Entrée structurée immuable :

| Champ | Règle |
|---|---|
| `type` | Obligatoire, valeur exacte de `PlaceType` |
| `parentPlaceId` | Null uniquement pour `country`; obligatoire pour les autres types |
| `cursor` | Optionnel, opaque, lié au fingerprint de la requête |
| `limit` | Obligatoire ou valeur applicative documentée ; entier borné 1–100 |

La paire parent/type doit être compatible avec `PlaceType::acceptsParent`. Aucune locale n'est acceptée : Geography ne possède pas de règle normative de traduction. Aucun texte libre, slug, city ou neighborhood label n'est une clé.

Sortie : résultat fermé, items ordonnés, `nextCursor` nullable. Le reader ne retourne jamais l'Aggregate `Place`.
