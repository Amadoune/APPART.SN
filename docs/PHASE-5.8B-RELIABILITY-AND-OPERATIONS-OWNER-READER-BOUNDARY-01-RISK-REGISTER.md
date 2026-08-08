# Phase 5.8B — Reliability & Operations — Owner Reader Risk Register

| Risque | Impact | Traitement requis |
|---|---|---|
| Missing assimilé à un statut opérationnel | décision implicite | ajouter une correspondance contractuelle explicite ou amender la source |
| Corrupted masqué | perte d'intégrité visible | statut public homonyme requis |
| DependencyUnavailable utilisé comme fallback | diagnostic falsifié | réserver au seul état homonyme |
| lecture Runtime | confusion disponibilité/source | interdiction Architecture future |
| accès PostgreSQL ou Mapper | contournement du port | dépendance au port Application seulement |
| agrégation des sept streams | nouvelle sémantique | un Owner Reader par stream |
| source secondaire | divergence d'autorité | source unique vérifiée |
| exposition de RevisionState | fuite interne | Result public limité à status/observedAt |
| ouverture prématurée de Foundation | implémentation non exhaustive | blocage normatif explicite |

Le risque de réduction non exhaustive est bloquant pour l'implémentation, mais n'empêche pas la certification du constat documentaire.
