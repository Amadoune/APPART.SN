# SecurityCompliance Delivery Foundation

La Foundation matérialise cinq familles Delivery V1 issues exclusivement des Events `SecretInventory`, `SecurityAudit`, `Incident`, `PrivacyPolicy` et `ComplianceControl`.

Chaque famille comprend `DeliveryV1`, `DeliveryFactory`, `DeliveryPayload`, `DeliveryStatus` et `DeliveryResult`, soit exactement 25 composants. Une Factory produit exactement une Delivery par Event.

Le type Event est conservé hors payload. Le payload contient uniquement le statut homonyme et `observedAt` recopié sans transformation. Cryptography Policy, Data Retention et Data Export restent sans famille Delivery.
