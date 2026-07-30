# Phase 4.7 — Administrative Action Lifecycle Delivery Consumer Specification

## Responsabilité

`AdministrativeActionLifecycleDeliveryConsumer` adapte un message Outbox
générique vers l'enveloppe Delivery 4.7F, appelle le routeur 4.7G puis applique
la politique 4.7G-R1. Il ne porte aucune décision Lifecycle.

## Validation avant routage

Le Consumer exige :

- un `AdministrativeActionLifecycleDeliveryPayload` restaurable exactement ;
- un des quatre types événementiels 4.7E ;
- la version Delivery V1 ;
- l'owner `AdministrationAudit` ;
- l'agrégat `AdministrativeActionLifecycle` ;
- une identité égale à `actionId` ;
- une version causale égale à celle de l'événement ;
- l'index événementiel égal à 1.

Une divergence retourne `DivergentPayload` sans appel au routeur.

## Matrice de consommation

| Résultat du routeur | Résultat de consommation |
|---|---|
| `Routed` | `Consumed` |
| `Deferred / RouteUnavailable` | `BlockedBySourceReadiness` |
| `RetryableFailure / TransferFailed` | `RetryableFailure` |
| `Rejected / UnsupportedEvent` | `UnsupportedEventType` |
| `Rejected / CorruptedEvent` | `DivergentPayload` |

## Registre Worker

Le registre générique contient exactement une inscription V1 pour chacun des
quatre types. Toutes pointent vers le même Consumer singleton. Aucun Worker
spécialisé et aucun nouveau mécanisme d'exécution ne sont introduits.
