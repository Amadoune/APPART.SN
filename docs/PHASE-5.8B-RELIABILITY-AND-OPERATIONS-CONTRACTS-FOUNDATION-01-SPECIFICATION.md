# Phase 5.8B — Reliability & Operations — Contracts V1 Specification

## Owner

`ReliabilityOperations` est l'owner unique des contrats. Il ne reçoit aucune autorité métier sur les capacités observées.

## Interfaces

- `ObservabilityReaderV1` ;
- `ServiceHealthReaderV1` ;
- `AlertingReaderV1` ;
- `ContinuityReaderV1` ;
- `MaintenanceOperationsReaderV1` ;
- `CapacityPlanningReaderV1` ;
- `OperationalReadinessReaderV1`.

Chaque interface expose uniquement `read(ReliabilityOperationsObservedAt)` et retourne son Result V1 dédié.

## Value Object

`ReliabilityOperationsObservedAt` normalise un `DateTimeImmutable` en UTC et produit la représentation canonique microseconde `Y-m-dTH:i:s.uZ`.

## Sémantique publique

Les statuts décrivent seulement un état public fermé. Ils ne transportent ni métrique, seuil, log, trace, alerte, runbook, contenu de queue, plan de backup, RTO/RPO, configuration ou diagnostic interne.

`DependencyUnavailable` constitue l'état fail-closed commun. Aucun fallback ni état par défaut n'est défini.
