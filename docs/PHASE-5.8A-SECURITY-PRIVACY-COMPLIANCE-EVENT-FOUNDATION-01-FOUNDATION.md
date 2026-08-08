# SecurityCompliance Event Foundation

La Foundation matérialise cinq familles Event V1 alimentées exclusivement par `SecretInventoryReaderV1`, `SecurityAuditReaderV1`, `IncidentReaderV1`, `PrivacyPolicyReaderV1` et `ComplianceControlReaderV1`.

Chaque famille contient un Event V1, une Factory, un Payload, un Type et un Status, soit 25 composants. Chaque Result public produit exactement un Event homonyme dont le payload contient uniquement `status` et `observedAt`.

Les familles Cryptography Policy, Data Retention et Data Export restent absentes. Aucun Provider, Transport, Routing, Delivery ou Outbox n'est créé.
