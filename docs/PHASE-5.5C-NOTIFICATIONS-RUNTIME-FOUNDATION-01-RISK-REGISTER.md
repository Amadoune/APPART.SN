# Notifications Runtime — Risk Register

| Risque | Maîtrise |
|---|---|
| Décision métier dans la Runtime | Catalogue limité à la disponibilité technique |
| Dépendance Infrastructure dans Application | Dépendance unique au port owner source |
| Fuite de diagnostic | Trois champs fermés uniquement |
| Exception technique | Réduction fail-closed vers `DependencyUnavailable` |
| Binding multiple ou eager | Singletons lazy et alias uniques |
| Altération de Persistence | Migration 079 figée par checksum |
