# Phase 4.8 — Inventaire final des migrations

| Migration | Owner | Structures | Rollback |
|---|---|---|---|
| 038 | Geography / Lifecycle | `place_lifecycle_transitions`, index d'intention et lecture courante | `038_place_lifecycle_workflow.down.sql` |
| 039 | Geography / Routing | `place_lifecycle_event_inbox` et ses index | `039_place_lifecycle_event_inbox.down.sql` |
| 040 | Geography / Outbox | messages, deliveries, cursors et replays PublicProjection | `040_geography_outbox_owner.down.sql` |

Garanties consolidées :

- migrations additives;
- structures exclusivement dans `geography`;
- rollbacks isolés;
- aucune table historique remplacée;
- aucune migration antérieure réécrite;
- séparation journal / Inbox / Outbox conservée.

Les migrations 038, 039 et 040 et leurs scripts de rollback sont gelés.
