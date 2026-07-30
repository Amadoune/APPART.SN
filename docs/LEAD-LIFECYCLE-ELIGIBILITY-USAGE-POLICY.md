# Lead Lifecycle Eligibility Usage Policy

| Opération | Preuve requise | Catalogues | Source indisponible |
|---|---:|---:|---|
| `CreateLead` | oui | lus | erreur technique explicite |
| `Deliver` | non | non lus | sans effet |
| `Reject` | non | non lus | sans effet |
| `Close` | non | non lus | sans effet |
| `Unknown` | non applicable | non lus | commande interdite |

Une transition ne réévalue jamais une preuve de création et ne transforme jamais une erreur technique en décision métier.
