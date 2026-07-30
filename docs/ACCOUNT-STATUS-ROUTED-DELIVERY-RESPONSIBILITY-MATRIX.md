# Account Status — Routed Delivery Responsibility Matrix

| Responsabilité | Propriétaire unique | Interdictions |
|---|---|---|
| Choisir `identity_access.account_status.lifecycle_facts` | Router 4.9H | Consumer, Worker et Outbox ne choisissent jamais |
| Construire la preuve à partir de la décision reçue | Boundary Factory générique V1 | Aucun lookup ni table de routage |
| Conserver message, destination et preuve | Contrat Routed Delivery V1 | Aucune mutation |
| Vérifier la cohérence de la preuve | Frontière Routed Consumer V1 | Aucun recalcul de destination |
| Restaurer le payload Account Status | Mapper générique étendu en 4.9J | Aucun payload spécialisé supplémentaire |
| Appliquer la consommation 4.9I | `AccountStatusDeliveryConsumer` existant | Aucun routage |
| Traduire le résultat vers le résultat générique | Entrée additive `consumeRouted` du Consumer existant | Aucune nouvelle décision métier |
| Persister et relire la livraison routée | Writer/Reader génériques en 4.9J | Aucun composant IdentityAccess spécialisé |
| Piloter la livraison | Worker générique | Aucun routage implicite |

## Séquence normative

```text
AccountStatusEventRouter::route(message)
→ Routed(destination, même message)
→ Boundary Factory générique
→ RoutedDeliveryMessageV1(message, destination, preuve)
→ Writer / Reader génériques
→ Worker générique
→ consumeRouted(routedDelivery)
→ vérification preuve
→ consume(message, destination) certifié 4.9I
→ traduction fermée du résultat
```

Le passage par `consume(message, destination)` conserve la frontière 4.9I.
`consumeRouted` est uniquement une entrée générique versionnée et additive.

## Résultats fermés

| Résultat 4.9I | Diagnostic | Résultat générique |
|---|---|---|
| `Consumed` | aucun | `Consumed` |
| `Rejected` | `UnsupportedMessage` | `UnsupportedEventType` |
| `Rejected` | `CorruptedMessage` | `PermanentFailure` |
| `Rejected` | `UnsupportedDestination` | `PermanentFailure` |
| Preuve V1 divergente | n/a | `DivergentPayload` |

Cette traduction appartient exclusivement à la frontière générique du Consumer
existant. Elle ne modifie pas la décision 4.9I.
