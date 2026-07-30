# Lead Lifecycle Outbox Owner Migration & Rollback Guide

Migration additive : `026_contacts_leads_outbox_owner.sql`.

Elle crée dans `contacts_leads` les tables `public_projection_outbox_messages`, `deliveries`, `cursors` et `replays`, ainsi que les index de claim et d'ordre.

Le rollback `026_contacts_leads_outbox_owner.down.sql` supprime uniquement ces quatre tables dans l'ordre respectant leurs dépendances. L'Inbox 025, le journal Lead et les six autres owners restent intacts.

La migration 005 ne doit jamais être réécrite. Toute évolution future de l'owner exige une migration additive distincte.
