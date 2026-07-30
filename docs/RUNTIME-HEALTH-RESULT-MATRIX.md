# Runtime Health Result Matrix

| Diagnostics | Criticité | Résultat |
|---|---|---|
| aucun | — | `Healthy` |
| au moins un diagnostic optionnel, aucun obligatoire | optionnelle | `Degraded` |
| au moins un diagnostic obligatoire | obligatoire | `Unavailable` |

Un résultat contient toujours la liste ordonnée des diagnostics. `Healthy`, `Degraded` et
`Unavailable` ne sont jamais retournés comme de simples booléens.

L'inspection ne réalise aucun Update, rebuild, parcours paginé, accès PostgreSQL, HTTP, réseau,
horloge ou écriture.
