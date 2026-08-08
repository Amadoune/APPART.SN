# Dependency Matrix

| Dépendance | Usage documentaire autorisé | Accès direct interdit |
|---|---|---|
| Baselines gelées 5.0–5.8C | intégrité, statut, compatibilité | code et migrations |
| CI qualité | résultats terminaux et horodatés | modification des tests |
| PostgreSQL production | plan de migration/rollback, capacité, restore | connexion ou SQL au Discovery |
| Hébergement, réseau, DNS, TLS | attestations de disponibilité et configuration | mutation de plateforme |
| Secrets et KMS | preuves de stockage et rotation | lecture des secrets |
| Monitoring et alerting | couverture, ownership et exercices | création de règles |
| Backup et DR | preuves de restauration et RPO/RTO | opération de restauration réelle |
| Fournisseurs externes | SLA, quotas, contacts et modes dégradés | changement de contrat/configuration |
| Support et astreinte | calendriers, escalades et runbooks | activation opérationnelle |

Toute dépendance sans preuve identifiable reste un risque bloquant potentiel.

