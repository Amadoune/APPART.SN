# Transport catalog

| Mutation | Domain event | Transport cible | Consumer |
|---|---|---|---|
| Rename | PlaceRenamed | payload Geography Rename V1 dans outbox existante | Public Geography refresh |
| Enable | PlaceEnabled/lifecycle enabled | transport lifecycle existant | Public Geography refresh |
| Disable | PlaceDisabled/lifecycle disabled | transport lifecycle existant | Public Geography refresh |
| Merge | PlaceMerged/lifecycle merged | transport lifecycle existant | Public Geography refresh |

Le transport conserve eventId, PlaceId, aggregateVersion, occurredAt et target pour Merge.
