# SecurityCompliance Owner Reader Foundation

La Foundation matérialise exclusivement cinq Readers owner-scoped alimentés par `SecurityComplianceOwnerSource` : `SecretInventoryOwnerReader`, `SecurityAuditOwnerReader`, `IncidentOwnerReader`, `PrivacyPolicyOwnerReader` et `ComplianceControlOwnerReader`.

La Policy commune implémente `SecurityComplianceOwnerReaderV1` et produit un `SecurityComplianceOwnerReaderResult` portant uniquement `SecurityComplianceOwnerReaderStatus`. Les Readers publics restituent exclusivement `status` et `observedAt`.

`CryptographyPolicyOwnerReader`, `DataRetentionOwnerReader` et `DataExportOwnerReader` ne sont pas créés, faute de source owner-scoped.
