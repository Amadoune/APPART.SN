# Phase 4.7G-R1 — Administrative Action Lifecycle Delivery Consumption Matrix

## Matrice fermée

| Routage | Diagnostic | Consommation | Acquittement |
|---|---|---|---|
| `Routed` | aucun | `Consumed` | autorisé |
| `Deferred` | `RouteUnavailable` | `BlockedBySourceReadiness` | interdit |
| `RetryableFailure` | `TransferFailed` | `RetryableFailure` | interdit |
| `Rejected` | `UnsupportedEvent` | `UnsupportedEventType` | interdit |
| `Rejected` | `CorruptedEvent` | `DivergentPayload` | interdit |

Cette matrice s'applique sans distinction aux quatre types certifiés :

* `administrative.action.lifecycle.recorded` ;
* `administrative.action.lifecycle.approval_requested` ;
* `administrative.action.lifecycle.approved` ;
* `administrative.action.lifecycle.rejected`.

## Consommateurs autorisés

4.7G-R1 ne crée aucun Consumer d'exécution. Le seul futur consommateur autorisable est l'adaptateur Delivery générique de l'owner `AdministrationAudit`, après certification de la compatibilité Outbox. Il devra déléguer exclusivement au routeur 4.7G puis à cette politique.
