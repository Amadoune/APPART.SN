# Exigences de preuve RC2

| Dimension | Preuve exigée |
|---|---|
| Navigateur | Chrome système qualifié, profil propre et sans extension |
| HTTP | URL, méthode, statut, résultat applicatif et identité non secrète |
| IAM | formulaire réel, POST réel, `Set-Cookie`, navigation authentifiée |
| UI | état visible aux checkpoints du parcours |
| Autorité | état relu depuis les stores PostgreSQL productifs |
| Commande | `commandId`, `occurredAt`, version et résultat fermé |
| Replay | identité et données canoniques strictement identiques |

Une capture UI seule ne certifie pas une mutation. Une ligne PostgreSQL seule ne certifie pas le parcours HTTP. Les deux doivent converger avec l'identité de commande.

La console doit rester sans erreur bloquante. Aucun secret ne figure dans les preuves. Search reste hors périmètre.
