# Administration Console — Freeze Report

## Périmètre gelé

Le gel couvre toutes les Foundations AdministrationConsole et leurs surfaces certifiées : contrats V1, Persistence owner-scoped, Runtime, Owner Readers, HTTP, Event, Delivery et Outbox.

Les composants, contrats, catalogues fermés, mappings, dépendances, bindings, routes, événements, deliveries, politiques Outbox et Repository PostgreSQL certifiés deviennent immuables dans le cadre de la phase 5.6.

## Migrations gelées

| Migration | État | Empreinte SHA-256 |
|---|---|---|
| `082_administration_console_owner_source.sql` | GO CERTIFIÉE — GELÉE | `ed6987ae4605d723d25b7e58b5148535e650d1bec397302dbb202da6abc3e544` |
| `082_administration_console_owner_source.down.sql` | GO CERTIFIÉE — GELÉE | `83f175e530ca739469e86d7c47a9773e0082d7c20f1424a36dfafb4493208fea` |
| `083_administration_console_outbox.sql` | GO CERTIFIÉE — GELÉE | `272cda32a365ccb019ba16d99d1e4765d289d306fc2a360ed70665766c433257` |
| `083_administration_console_outbox.down.sql` | GO CERTIFIÉE — GELÉE | `0e8481d304e03e9f71aeafbed81c89a23023ab2423d707ffe9c03126d8eefd75` |

Aucune migration ne reste ouverte après ce jalon.

## Gouvernance du gel

Toute évolution future exige un amendement versionné et une nouvelle décision d'autorité. Transport, Routing et Consumer demeurent hors périmètre, NON OUVERTS et non certifiés.
