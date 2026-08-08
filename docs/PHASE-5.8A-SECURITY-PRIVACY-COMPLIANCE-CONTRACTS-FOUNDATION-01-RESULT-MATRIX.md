# Result Matrix — SecurityCompliance Contracts V1

| Reader V1 | Result V1 | Catalogue Status V1 |
|---|---|---|
| SecretInventory | SecretInventoryResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| CryptographyPolicy | CryptographyPolicyResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| SecurityAudit | SecurityAuditResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| Incident | IncidentResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| PrivacyPolicy | PrivacyPolicyResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| DataRetention | DataRetentionResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| DataExport | DataExportResultV1 | Available, Missing, Corrupted, DependencyUnavailable |
| ComplianceControl | ComplianceControlResultV1 | Available, Missing, Corrupted, DependencyUnavailable |

Les huit catalogues sont des enums distinctes, fermées et versionnées V1, totalisant 32 états contextuels. Le catalogue minimal évite d'exposer une configuration, un contenu sensible ou une décision détaillée non autorisée.

Aucun fallback ou statut générique non typé n'est permis.
