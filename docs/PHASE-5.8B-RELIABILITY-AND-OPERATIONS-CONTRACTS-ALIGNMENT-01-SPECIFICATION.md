# Phase 5.8B — Reliability & Operations — Contracts Alignment Specification

## Objet exclusif

Aligner les sept catalogues Status V1 sur les quatre états structurels de la source owner-scoped : Found, Missing, Corrupted et DependencyUnavailable.

`Found` continue de transporter uniquement une décision homonyme déjà fermée par stream. `Missing`, `Corrupted` et `DependencyUnavailable` disposent désormais chacun d'un statut public homonyme.

## Modifications

- ObservabilityStatusV1 reçoit `Corrupted` ; `Missing` existait déjà.
- Les six autres Status V1 reçoivent `Missing` et `Corrupted`.
- Readers, Results, signatures, Value Object et statuts métier existants restent inchangés.

Aucun fallback, aucune agrégation, aucune nouvelle décision métier et aucune implémentation ne sont introduits.
