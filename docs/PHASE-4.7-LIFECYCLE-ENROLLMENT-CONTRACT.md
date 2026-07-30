# Phase 4.7B-R1 — Lifecycle Enrollment Contract

## Checkpoint

`AdministrativeActionLifecycleEnrollmentCheckpoint` contient exactement :

- `AdministrativeActionId` ;
- version historique, supérieure ou égale à zéro ;
- état Lifecycle exact ;
- checksum SHA-256 de la source historique canonique.

## Résultats fermés

- `Enrolled` ;
- `AlreadyEnrolled` ;
- `SourceMissing` ;
- `SourceUnavailable` ;
- `SourceCorrupted` ;
- `EnrollmentDivergence` ;
- `PersistenceCorrupted`.

Une absence de source n'est jamais une corruption. Une indisponibilité ne devient jamais `SourceMissing`. Aucun fallback ne fabrique un checkpoint.
