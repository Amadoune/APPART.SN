# Notifications Owner Reader — Dependency Matrix

| Composant futur | Dépendance autorisée | Dépendances interdites |
|---|---|---|
| Preference Owner Reader | `NotificationsOwnerSource` | Runtime, HTTP, Infrastructure, source externe |
| Template Owner Reader | `NotificationsOwnerSource` | Runtime, HTTP, Infrastructure, source externe |
| Channel Owner Reader | `NotificationsOwnerSource` | Runtime, HTTP, Infrastructure, source externe |

Sont également interdits : Provider, binding, SQL, PostgreSQL, mapper, Event, Delivery, Outbox, Consumer, Transport et Routing dans la frontière Application.
