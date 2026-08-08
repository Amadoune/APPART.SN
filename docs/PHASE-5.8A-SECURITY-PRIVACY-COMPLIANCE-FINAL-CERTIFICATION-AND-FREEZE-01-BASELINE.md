# Final Certification & Freeze 5.8A — Baseline

## Owner et chaînes certifiées

L'owner unique est `SecurityCompliance`, sans autorité métier transverse. Les cinq chaînes consolidées sont :

1. `SecurityComplianceOwnerSource → SecretInventoryOwnerReader → SecretInventoryReaderV1 → HTTP → SecretInventoryEventV1 → SecretInventoryDeliveryV1 → Outbox` ;
2. `SecurityComplianceOwnerSource → SecurityAuditOwnerReader → SecurityAuditReaderV1 → HTTP → SecurityAuditEventV1 → SecurityAuditDeliveryV1 → Outbox` ;
3. `SecurityComplianceOwnerSource → IncidentOwnerReader → IncidentReaderV1 → HTTP → IncidentEventV1 → IncidentDeliveryV1 → Outbox` ;
4. `SecurityComplianceOwnerSource → PrivacyPolicyOwnerReader → PrivacyPolicyReaderV1 → HTTP → PrivacyPolicyEventV1 → PrivacyPolicyDeliveryV1 → Outbox` ;
5. `SecurityComplianceOwnerSource → ComplianceControlOwnerReader → ComplianceControlReaderV1 → HTTP → ComplianceControlEventV1 → ComplianceControlDeliveryV1 → Outbox`.

Les résultats, réponses HTTP, Events, Deliveries et messages Outbox restent limités aux données publiques minimales. Aucun secret, clé, PII, configuration sensible, SubjectKey ou RevisionState n'est propagé. `observedAt` UTC est conservé.

## Familles contractuelles non matérialisées

`CryptographyPolicy`, `DataRetention` et `DataExport` demeurent absentes des surfaces Owner Reader, HTTP, Event, Delivery et Outbox faute de source owner-scoped certifiée. Aucun placeholder ou contournement n'est autorisé.

## Surfaces non ouvertes

- Transport : **NON OUVERT**.
- Routing : **NON OUVERT**.
- Consumer : **NON OUVERT**.
- Phase 5.8B : **NON OUVERTE**.

## Empreintes du worktree

| Fichier | SHA-256 |
|---|---|
| 084_legacy_migration_owner_source.sql | A0450F8D6553C4FC61E924DED877D45E118F987115DEC49532AE7F6F878B7591 |
| 084_legacy_migration_owner_source.down.sql | E7CA6A6C825FE1F209EF8283EB1DAA6F9F653631903783014C0E6F362CA41D4D |
| 085_legacy_migration_outbox.sql | C13D421DB86E0E3AE67F9B20E677A61C9A4BC430441BFA811977E5DDA744C994 |
| 085_legacy_migration_outbox.down.sql | 6576DF3DEA08C862CC796AA73497B55BC48F4FCB67FE814462D773A65F3E83DF |
| 086_security_compliance_owner_source.sql | B0E5E97D49F4B9F9F901DB9E4FF61D6C12E50BA67E66C300BB9A0E610803EE93 |
| 086_security_compliance_owner_source.down.sql | 113B3965A12D9C7927CC596FB6DFC1607E85F11E71AC50F0C546E30EEAC97E57 |
| 087_security_compliance_outbox.sql | 56B9CE96E101729BC48471B9ADEEF17566215230F150775A5147C37BE5F9C107 |
| 087_security_compliance_outbox.down.sql | E42C805AB0B407113884385754D852CEFCFEB84FE8C0D194D8412B0DA8513389 |

Les migrations 084 à 087 et leurs rollbacks sont inchangés par ce jalon.
