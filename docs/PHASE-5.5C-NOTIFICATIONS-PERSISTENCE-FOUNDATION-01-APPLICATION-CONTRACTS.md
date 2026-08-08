# Notifications Persistence — Application Contracts

`NotificationsOwnerSource` expose les append et lectures temporelles des trois streams. Les Revision States portent clé opaque, révision, décision, instant effectif et instant enregistré. Les Write Results ferment `Applied`, `AlreadyApplied`, `DivergentRevision`, `VersionConflict`, `Corrupted` et `DependencyUnavailable`. Les Read Results échouent fermés et associent un état uniquement à un résultat trouvé.
