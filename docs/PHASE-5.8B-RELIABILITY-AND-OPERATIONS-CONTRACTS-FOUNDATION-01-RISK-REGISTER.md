# Phase 5.8B — Reliability & Operations — Contracts Risk Register

| Risque | Traitement contractuel | Résiduel |
|---|---|---|
| statut interprété comme décision métier | noms techniques et documentation explicite | faible |
| exposition de payload opérationnel | Results limités à status et observedAt | faible |
| fuite de PII ou secret | aucune propriété libre | faible |
| catalogue ambigu | enum dédié par Reader | faible |
| fallback implicite | aucun default et DependencyUnavailable explicite | faible |
| confusion de périmètres | Continuity et MaintenanceOperations documentés | moyen |
| cardinalité ou métriques exposées | aucune valeur quantitative | faible |
| couplage à une capacité gelée | aucune dépendance cross-module | faible |
| ouverture aval implicite | interdiction normative explicite | faible |

Le risque résiduel moyen sur les regroupements devra être réévalué avant toute Persistence ou Runtime Foundation.
