# Phase 5.3L — Matrice migrations et rollbacks

| Migration | Owner | Objet | Appliquée par la campagne | Rollback | Rejeu ciblé | Dépendances |
|---|---|---|---|---|---|---|
| 063 | ModerationReports | cases, reports, findings, decisions, queue | oui | disponible | certifié | owner-local |
| 064 | ModerationReports | intents de claims Queue | oui | disponible | certifié | 063 |
| 065 | ModerationReports | ledger Event Delivery | oui | disponible | certifié | 063 |
| 066 | ModerationReports | frontière Atomic Outbox | oui | disponible | certifié | 063 |
| 067 | ModerationReports | Outbox Event propriétaire | oui | disponible | certifié | 063, 066 |
| 068 | Listing | intents de commandes Moderation Listing | oui | disponible | certifié | schéma Listing |
| 069 | ModerationReports | résultats Listing Handoff | oui | disponible | certifié | 063, 067 |
| 070 | ModerationReports | index source owner Queue | oui | disponible | certifié | 063 |
| 071 | AdministrationAudit | append public durable | oui | disponible | certifié | owner-local |

## Contrôles

- séquence numérique sans collision ;
- migrations additives ;
- owner explicite ;
- aucun trigger, cascade ou FK cross-domain ;
- fichiers `.sql` et `.down.sql` présents pour 063 à 071 ;
- aucun SQL certifié modifié par 5.3L.

Le rejeu PostgreSQL complet post-amendements confirme cette matrice :
680 tests, 3 070 assertions, PASS, exit code 0. Les dix-huit fichiers `.sql`
et `.down.sql` ont été inventoriés et empreintés en SHA-256 ; aucun fichier SQL
n'a été modifié par 5.3L.
