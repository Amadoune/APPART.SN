# Legacy Migration & Reconciliation Contracts V1 — Specification

## Owner et autorité

`LegacyMigration` est l'owner unique de coordination. Les contrats exposent des observations de coordination uniquement et ne prennent aucune décision de migration. Les owners cibles restent les seules autorités métier.

## Contrats read-only

- `LegacyMigrationInventoryReaderV1` ;
- `LegacyMigrationWaveReaderV1` ;
- `LegacyMigrationReconciliationReaderV1` ;
- `LegacyMigrationQuarantineReaderV1` ;
- `LegacyMigrationCutoverReaderV1`.

Chaque interface expose uniquement `read(LegacyMigrationSubjectKey, LegacyMigrationObservedAt)` et retourne le Result V1 fermé correspondant.

## Value Objects

`LegacyMigrationSubjectKey` est une clé opaque, non vide, sans espaces périphériques et limitée à 255 caractères. Elle sert à adresser une observation et n'est jamais renvoyée dans le résultat.

`LegacyMigrationObservedAt` normalise l'instant en UTC et le rend canonique à la microseconde sous la forme `Y-m-dTH:i:s.uZ`.

## Résultats

Chaque Result V1 expose exclusivement :

- `status`, issu de son enum fermé ;
- `observedAt`, chaîne UTC canonique.

Aucune PII, aucun identifiant Legacy, aucune donnée métier, volumétrie, règle de transformation ou décision n'est exposé.
