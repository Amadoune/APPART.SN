# Notifications Persistence Foundation

`Notifications` est l'owner unique. Le journal append-only `notifications.owner_revision_journal` est l'unique autorité durable ; `owner_current_index` est strictement dérivé. Les streams `preference`, `template` et `channel` sont indépendants.

Chaque append verrouille son stream, contrôle une révision positive et monotone, écrit le journal puis actualise l'index dans une transaction owner-locale atomique. Une transaction appelante reste propriétaire de son rollback grâce à un savepoint local.
