# Professional Status Delivery Payload Specification

Le payload Delivery implémente `PublicProjectionDeliveryPayload` et expose exactement :

```text
canonicalEvent
```

Le checksum est le SHA-256 des octets de `canonicalEvent`. La restauration refuse tout champ supplémentaire, JSON non canonique, incohérence d'identité, type invalide ou altération du contrat 4.5E.
