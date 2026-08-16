# Failure Modes

| Situation | Décision |
|---|---|
| fait Listing requis absent | `SourceMissing`, aucune sortie |
| fait Listing corrompu/incohérent | `SourceCorrupted`, aucune sortie |
| état Listing inconnu ou non supporté | `UnsupportedFacts`, aucune sortie |
| rang hors 0..10000 | `InvalidRank`, défaut de policy |
| facette non vide en v1 | `InvalidFacet`, défaut de policy |
| lecture owner indisponible | `DependencyUnavailable`, aucune sortie |

Ces statuts sont un modèle documentaire fermé à qualifier lors de la matérialisation ; aucun runtime n'est créé.
