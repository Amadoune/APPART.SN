# Source Inventory — Legacy Migration Owner Reader Boundary

## Source candidate unique

`LegacyMigrationOwnerSource` est l'unique source autorisée. Elle expose cinq lectures indépendantes : Inventory, Wave, Reconciliation, Quarantine et Cutover.

| Source examinée | Qualification | Motif |
|---|---|---|
| `LegacyMigrationOwnerSource` | autorisée | port owner-scoped certifié |
| repository PostgreSQL | interdite | détail Infrastructure |
| mapper | interdit | reconstruction interne |
| Runtime V1 | interdit | disponibilité technique seulement |
| autre owner ou source | interdit | rupture d'autorité |

Les Revision States, révisions, checksums et métadonnées de journal demeurent internes. Aucun inventaire Legacy brut n'est exposé.
