# Event Matrix

| Famille | Type V1 | Statuts |
|---|---|---|
| SecretInventory | `security-compliance.secret-inventory.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable |
| SecurityAudit | `security-compliance.security-audit.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable |
| Incident | `security-compliance.incident.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable |
| PrivacyPolicy | `security-compliance.privacy-policy.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable |
| ComplianceControl | `security-compliance.compliance-control.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable |

Les vingt réductions Result → Event sont exhaustives, mécaniques, bijectives et homonymes, sans fallback ni agrégation. `observedAt` est recopié sans transformation.
