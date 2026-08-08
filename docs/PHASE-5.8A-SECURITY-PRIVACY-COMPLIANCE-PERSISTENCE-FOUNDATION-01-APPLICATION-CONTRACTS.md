# Application Contracts

| Stream | Revision State | Read Result | Write Result |
|---|---|---|---|
| SecretInventory | `SecretInventoryRevisionState` | `SecretInventoryReadResult` | `SecretInventoryWriteResult` |
| SecurityAudit | `SecurityAuditRevisionState` | `SecurityAuditReadResult` | `SecurityAuditWriteResult` |
| Incident | `IncidentRevisionState` | `IncidentReadResult` | `IncidentWriteResult` |
| PrivacyPolicy | `PrivacyPolicyRevisionState` | `PrivacyPolicyReadResult` | `PrivacyPolicyWriteResult` |
| ComplianceControl | `ComplianceControlRevisionState` | `ComplianceControlReadResult` | `ComplianceControlWriteResult` |

`SecurityComplianceOwnerSource` expose exclusivement les cinq opérations append et les cinq lectures temporelles correspondantes. L'Application ne dépend ni de PDO, ni de PostgreSQL, ni d'une Foundation ultérieure.
