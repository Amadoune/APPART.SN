# Property Lifecycle Transition Matrix

| État source | Action | État cible |
|---|---|---|
| Draft | Activate | Active |
| Draft | Archive | Archived |
| Active | BeginMaintenance | UnderMaintenance |
| Active | MarkUnavailable | Unavailable |
| Active | Decommission | Decommissioned |
| UnderMaintenance | CompleteMaintenance | Active |
| UnderMaintenance | MarkUnavailable | Unavailable |
| UnderMaintenance | Decommission | Decommissioned |
| Unavailable | RestoreAvailability | Active |
| Unavailable | BeginMaintenance | UnderMaintenance |
| Unavailable | Decommission | Decommissioned |
| Decommissioned | Archive | Archived |

Les 36 autres combinaisons état/action sont refusées et ne contiennent aucune transition.
