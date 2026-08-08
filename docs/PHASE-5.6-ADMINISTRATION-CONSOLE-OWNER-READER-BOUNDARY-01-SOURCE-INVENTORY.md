# Administration Console Owner Reader — Source Inventory

| Élément | Statut | Usage autorisé |
|---|---|---|
| `AdministrationConsoleOwnerSource` | Source candidate unique | `readOperator`, `readQueue` et `readAudit` |
| Operator stream via le port source | Autorisé | Réduction vers `AdministrationOperatorReaderV1` |
| Queue stream via le port source | Autorisé | Réduction vers `AdministrationQueueReaderV1` |
| Audit stream via le port source | Autorisé | Réduction vers `AdministrationAuditReaderV1` |
| Implémentation PostgreSQL de la source | Interdite comme dépendance directe | Masquée derrière le port Application |
| Runtime Administration Console | Interdit comme source | Disponibilité technique, aucune décision métier |
| Toute projection, cache ou source externe | Interdit | Seconde autorité ou fallback non autorisé |
| IAM, Moderation, Notifications, ContentSeo et autres domaines | Interdits comme sources | Aucun accès cross-domain |

Aucune autre source candidate n'est recevable. Aucun mécanisme de reconstruction, de recoupement ou d'enrichissement n'est autorisé.
