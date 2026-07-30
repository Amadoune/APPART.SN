# Lead Lifecycle Delivery Payload Specification

`LeadLifecycleDeliveryPayload` implémente `PublicProjectionDeliveryPayload` et expose exactement :

```text
canonicalEvent: string
```

Cette chaîne est la sérialisation canonique 4.4E. Son checksum est `SHA-256(canonicalEvent)`. La restauration vérifie la forme, les types, l'ordre, le catalogue, l'identité et la reproduction exacte des octets.

Aucune donnée n'est ajoutée au payload métier.
