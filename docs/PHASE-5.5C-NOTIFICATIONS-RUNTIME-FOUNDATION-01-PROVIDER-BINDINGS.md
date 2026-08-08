# Notifications Runtime — Provider Bindings

`NotificationsRuntimeServiceProvider` enregistre des singletons lazy et des alias uniques :

- `PostgreSqlNotificationsOwnerSource` vers `NotificationsOwnerSource` ;
- `DeterministicNotificationsRuntimeAvailabilityPolicy` vers `NotificationsRuntimeAvailabilityPolicy` ;
- `DeterministicNotificationsRuntime` vers `NotificationsRuntimeV1`.

La Runtime Application dépend exclusivement du port `NotificationsOwnerSource`.
