# Listing Publication Consumer Outcome Matrix

| Résultat du routeur | Signification | Acquittement futur autorisé | Disposition attendue en 4.1EB |
|---|---|---|---|
| `Routed` | transfert réel confirmé | Oui | `Consumed` uniquement après confirmation |
| `Deferred` | aucune route durable disponible | Non | conserver ou différer explicitement |
| `RetryableFailure` | tentative de transfert échouée | Non | planifier un retry |
| `Rejected` | événement non routable ou corrompu | Non | disposition terminale explicite |

Aucun résultat autre que `Routed` ne peut être assimilé à une consommation réussie. Le Sprint 4.1EBA ne réalise encore aucun de ces mappings dans le Worker.
