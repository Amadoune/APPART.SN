# Specification — SecurityCompliance Contracts V1

## Owner et portée

`SecurityCompliance` est l'owner unique des contrats publics de cette capacité, sans autorité métier sur les autres domaines. La Foundation contient exclusivement des interfaces read-only et leurs types publics V1.

## Contrats

- `SecretInventoryReaderV1` ;
- `CryptographyPolicyReaderV1` ;
- `SecurityAuditReaderV1` ;
- `IncidentReaderV1` ;
- `PrivacyPolicyReaderV1` ;
- `DataRetentionReaderV1` ;
- `DataExportReaderV1` ;
- `ComplianceControlReaderV1`.

Chaque interface expose uniquement `read(SecurityComplianceSubjectKey, SecurityComplianceObservedAt)` et retourne son Result V1 dédié.

## Value Objects

`SecurityComplianceSubjectKey` est une clé opaque, canonique, non vide et limitée à 255 caractères. `SecurityComplianceObservedAt` normalise toute valeur en UTC et la restitue au format canonique microseconde.

## Minimisation

Chaque Result expose exactement `status` et `observedAt`. Aucun secret, clé, PII, contenu d'audit, politique détaillée, configuration, identifiant interne ou donnée métier n'est transporté.
