# External Dependency Readiness

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| PostgreSQL | Data Owner | version cible, HA, capacité, backup | DSN de test et exigence pgsql | .env.postgresql.example, composer.json | PARTIAL | production non décrite | attestation plateforme |
| Redis | Platform Owner | version, HA, mémoire, alertes | variables génériques | .env.example | PARTIAL | usage et service cible inconnus | qualifier dépendance |
| Mail | Product Operations | fournisseur, quotas, SPF/DKIM, fallback | configuration log/local | .env.example, config/mail.php | MISSING | aucune readiness fournisseur | attestation externe |
| Object storage | Media Owner | bucket, IAM, lifecycle, backup | variables AWS vides | .env.example | MISSING | stockage production inconnu | qualification externe |
| DNS/TLS | Platform Owner | zones, certificats, renouvellement | aucune | aucun | MISSING | accès public non démontré | preuve fournisseur |
| Hébergement | Platform Owner | région, ressources, SLA, support | aucune | aucun | MISSING | environnement cible absent | dossier plateforme |
| Monitoring | ReliabilityOperations | backend, SLA, contacts | aucune | aucun | MISSING | supervision externe absente | contrat/configuration probante |
| Parties UAT | ExperienceAcceptance | approbateurs nommés | aucune | aucun | MISSING | acceptation non traçable | nomination et signature |

