# Property Lifecycle Transition to Event Matrix

| Transition | Événement |
|---|---|
| Draft → Activate → Active | `property.lifecycle.activated` |
| Draft → Archive → Archived | `property.lifecycle.archived` |
| Active → BeginMaintenance → UnderMaintenance | `property.lifecycle.maintenance_started` |
| Active → MarkUnavailable → Unavailable | `property.lifecycle.marked_unavailable` |
| Active → Decommission → Decommissioned | `property.lifecycle.decommissioned` |
| UnderMaintenance → CompleteMaintenance → Active | `property.lifecycle.maintenance_completed` |
| UnderMaintenance → MarkUnavailable → Unavailable | `property.lifecycle.marked_unavailable` |
| UnderMaintenance → Decommission → Decommissioned | `property.lifecycle.decommissioned` |
| Unavailable → RestoreAvailability → Active | `property.lifecycle.availability_restored` |
| Unavailable → BeginMaintenance → UnderMaintenance | `property.lifecycle.maintenance_started` |
| Unavailable → Decommission → Decommissioned | `property.lifecycle.decommissioned` |
| Decommissioned → Archive → Archived | `property.lifecycle.archived` |

Chaque ligne produit exactement un événement. Toute autre combinaison est explicitement refusée.
