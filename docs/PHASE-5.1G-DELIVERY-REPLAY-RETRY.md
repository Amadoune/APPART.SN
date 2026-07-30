# Phase 5.1G — Delivery, Replay & Retry

Le consumer valide de nouveau le transport puis vérifie le couple event/destination avant de produire un fait consommé minimal.

| Outcome | Décision |
|---|---|
| consumed / already consumed | complete |
| transient failure, tentative 1–4 | retry |
| transient failure, tentative 5 | quarantine |
| permanent failure | quarantine |
| divergent replay | quarantine |

Un replay identique est terminal et sans nouvel effet. Un replay divergent n'est jamais retryé. Le plafond de cinq tentatives est déterministe.

La persistance de publication atomique et l'Outbox restent réservées à 5.1H.
