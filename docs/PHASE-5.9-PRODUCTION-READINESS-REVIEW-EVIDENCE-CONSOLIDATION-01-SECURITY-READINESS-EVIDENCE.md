# Security Readiness Evidence

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Dépendances | SecurityCompliance | audits actuels PHP/JS | script Composer et anciennes attestations npm | composer.json ; docs/PHASE-5.2A-QUALITY-BASELINE.md | PARTIAL | résultats obsolètes/non liés au candidat | audits datés requis |
| Secrets | SecurityCompliance | KMS, rotation, accès | variables exemples | .env.example | PARTIAL | plateforme secrets inconnue | attestation sans divulgation |
| Permissions applicatives | Domain Owners | tests et matrice | tests/architecture historiques | tests ; docs sécurité | PARTIAL | couverture prod non consolidée | matrice finale |
| IAM plateforme/DB | Platform Owner | rôles least privilege | rôle de test uniquement documenté | .env.postgresql.example | MISSING | permissions prod inconnues | qualification externe |
| TLS/DNS | Platform Owner | certificats, renouvellement, DNS | non observé | aucun | MISSING | surface publique non qualifiée | preuve fournisseur |
| Vulnérabilités | SecurityCompliance | scan, triage et acceptation explicite | non observé | aucun | MISSING | risques non quantifiés | campagne séparée |
| Incident sécurité | SecurityCompliance | runbook et exercice | non observé | aucun | MISSING | réponse non démontrée | runbook/exercice |

