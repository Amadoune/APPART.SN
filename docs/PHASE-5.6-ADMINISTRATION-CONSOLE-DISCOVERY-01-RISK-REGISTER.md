# Administration Console — Risk Register

| Risque | Maîtrise recommandée |
|---|---|
| Super-owner administratif | Décisions maintenues dans les domaines sources |
| Contournement IAM | Principal et permissions fournis exclusivement par IAM |
| Duplication des files | Files owner-scoped ; vues console reconstructibles |
| Lecture directe des bases | Dépendances limitées aux ports versionnés |
| Audit incomplet | Append obligatoire vers AdministrationAudit |
| Fuite de PII | Vues minimisées, autorisées et observées explicitement |
| Couplage aux Runtimes | Runtime Health et diagnostics internes interdits |
| Actions non idempotentes | Identité d'intention et concurrence à qualifier ultérieurement |
