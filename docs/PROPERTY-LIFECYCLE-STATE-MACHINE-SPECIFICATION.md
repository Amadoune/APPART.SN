# Property Lifecycle State Machine Specification

La machine comporte six états, huit actions dont `Unknown`, et douze transitions autorisées. `Archived` est terminal. `Decommissioned` n'autorise plus que l'archivage.

```mermaid
stateDiagram-v2
    [*] --> Draft
    Draft --> Active: activate
    Draft --> Archived: archive
    Active --> UnderMaintenance: begin_maintenance
    Active --> Unavailable: mark_unavailable
    Active --> Decommissioned: decommission
    UnderMaintenance --> Active: complete_maintenance
    UnderMaintenance --> Unavailable: mark_unavailable
    UnderMaintenance --> Decommissioned: decommission
    Unavailable --> Active: restore_availability
    Unavailable --> UnderMaintenance: begin_maintenance
    Unavailable --> Decommissioned: decommission
    Decommissioned --> Archived: archive
    Archived --> [*]
```

Toute paire absente est refusée explicitement. Aucune branche par défaut n'existe.
