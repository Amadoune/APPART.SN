# Phase 4.6 — Dependency Matrix

| Dépendance | Usage autorisé | Usage interdit |
|---|---|---|
| `MediaCollection` | source propriétaire d'une décision de remplacement explicite | exécution ou reconstruction dans le workflow pur |
| `MediaCollectionRegistry` | source certifiée lors d'un futur gate dédié | accès direct depuis HTTP, Event ou transport |
| PostgreSQL Runtime | transaction et persistance dans les sprints dédiés | accès au sprint Workflow |
| Property | identité déjà portée par la collection | lecture de Property Lifecycle ou dérivation d'éligibilité |
| Public Projection Delivery | transport générique futur | modification du contrat événementiel métier |
| Worker générique | inscriptions futures après politique de consommation | Worker Media parallèle |
| Runtime Health | inspection structurelle des bindings composés | appel métier ou SQL au bootstrap |

Les capacités 4.1 à 4.5 sont des précédents architecturaux, jamais des sources de règles à copier.
