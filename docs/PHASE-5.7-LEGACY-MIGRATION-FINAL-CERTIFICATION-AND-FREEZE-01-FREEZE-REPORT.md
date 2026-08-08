# Freeze Report — Phase 5.7

## Surfaces gelées

- les cinq contrats publics Reader V1, leurs Value Objects, Results et catalogues fermés ;
- `LegacyMigrationOwnerSource`, ses cinq streams, le mapper et le repository PostgreSQL ;
- le Runtime technique et ses diagnostics minimaux ;
- les cinq Owner Readers et leurs bindings publics ;
- les cinq Controllers, Requests, routes et mappings HTTP ;
- les cinq familles Events V1 ;
- les cinq familles Deliveries V1 ;
- l'Outbox owner-scoped, sa Policy et son repository PostgreSQL.

## Migrations gelées

| Fichier | SHA-256 | État |
|---|---|---|
| `084_legacy_migration_owner_source.sql` | `a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591` | GO CERTIFIÉE — GELÉE |
| `084_legacy_migration_owner_source.down.sql` | `e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d` | GO CERTIFIÉE — GELÉE |
| `085_legacy_migration_outbox.sql` | `c13d421db86e0e3ae67f9b20e677a61c9a4bc430441bfa811977e5dda744c994` | GO CERTIFIÉE — GELÉE |
| `085_legacy_migration_outbox.down.sql` | `6576df3dea08c862cc796aa73497b55bc48f4fcb67fe814462d773a65f3e83df` | GO CERTIFIÉE — GELÉE |

Aucune migration ne reste ouverte. Toute modification future d'une surface ou migration gelée exige un amendement versionné, explicitement ouvert et certifié.
