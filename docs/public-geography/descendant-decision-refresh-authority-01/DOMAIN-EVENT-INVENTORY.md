# Domain event inventory

`PlaceRenamed`, `PlaceEnabled`, `PlaceDisabled`, `PlaceMerged` portent PlaceId, occurredAt et aggregateVersion; Rename porte ancien/nouveau nom, Merge la target. Create n'affecte aucune décision existante.

Enabled/Disabled/Merged disposent du catalogue `PlaceLifecycleEventV1` avec eventId déterministe et occurredVersion. Rename existe au Domain mais n'est pas sérialisé/livré par ce catalogue.
