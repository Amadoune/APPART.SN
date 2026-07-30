# Property Lifecycle Identity Separation Specification

| Identité | Propriétaire | Responsabilité |
|---|---|---|
| `eventId` | contrat métier Property Lifecycle | identifier durablement le fait métier persisté |
| `message_id` | Public Projection Delivery | identifier une future unité technique de livraison |

`eventId` reste inclus dans `canonicalEvent`. Il ne devient jamais `message_id` et n'est pas dérivé de celui-ci. Le futur message Delivery conservera simultanément les deux identités.

Le préfixe métier est `property-lifecycle-`; le préfixe Delivery certifié est `ppd-message:`. Leur dérivation et leur espace de responsabilité restent indépendants.
