# Delivery Matrix

| Event V1 | Delivery V1 | Type | Payload |
|---|---|---|---|
| SecretInventoryEventV1 | SecretInventoryDeliveryV1 | type Event conservé | status, observedAt |
| SecurityAuditEventV1 | SecurityAuditDeliveryV1 | type Event conservé | status, observedAt |
| IncidentEventV1 | IncidentDeliveryV1 | type Event conservé | status, observedAt |
| PrivacyPolicyEventV1 | PrivacyPolicyDeliveryV1 | type Event conservé | status, observedAt |
| ComplianceControlEventV1 | ComplianceControlDeliveryV1 | type Event conservé | status, observedAt |

Les vingt propagations Available, Missing, Corrupted et DependencyUnavailable sont exhaustives, mécaniques, bijectives et homonymes, sans fallback, agrégation ou décision supplémentaire.
