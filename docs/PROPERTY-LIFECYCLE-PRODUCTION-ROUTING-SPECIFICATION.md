# Property Lifecycle Production Routing Specification

Le graphe de production est :

`PropertyLifecycleEventRouter → DurablePropertyLifecycleEventRouter → PropertyLifecycleEventDestination → PostgreSqlPropertyLifecycleEventInbox`

La destination conserve `inbox_id`, `event_id`, le type, la version, `canonical_event`, son checksum, le statut et le nombre de tentatives. Le statut initial est toujours `pending` et les tentatives valent zéro.

La réinsertion d'un `event_id` compare l'identifiant Inbox dérivé, le type, la version, le JSON canonique et le checksum. Une égalité complète retourne `AlreadyStored`; toute divergence retourne `Rejected`.
