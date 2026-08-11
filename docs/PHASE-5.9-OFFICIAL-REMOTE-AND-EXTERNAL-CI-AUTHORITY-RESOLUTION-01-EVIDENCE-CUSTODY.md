# Official Remote and External CI Authority Resolution 01 — Evidence Custody

Les responsabilités suivantes doivent être explicitement attribuées avant toute campagne externe :

| Preuve | Producteur | Conservation requise | État |
|---|---|---|---|
| Identité remote/owner | Autorité projet | Registre normatif durable | MISSING |
| Identité tag/commit | Owner Git | Référence Git immuable et attestation | BLOCKED |
| Run ID / attempt / conclusion | GitHub Actions | URL permanente et export | BLOCKED |
| Logs | Exécuteur externe | Export durable, sans secrets | BLOCKED |
| Archive / manifeste / checksums | Workflow R5 | Stockage durable avec provenance | BLOCKED |
| Attestation indépendante | Second opérateur | Dossier probatoire séparé | MISSING |

La rétention de 30 jours déclarée par le workflow est une capacité technique temporaire, pas une politique de conservation durable. Aucun custodian n'est actuellement désigné.
