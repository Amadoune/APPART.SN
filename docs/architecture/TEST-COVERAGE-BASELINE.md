# Test Coverage Baseline — Sprint 2.2

## État observé

| Périmètre | Classes | Méthodes de test |
|---|---:|---:|
| Tests métier des 12 modules implémentés | 37 | 385 |
| Architecture après Sprint 2.2 | 19 tests exécutés | 10 033 assertions |
| LegacyMigration | 0 | 0 |

Répartition des méthodes métier : AdministrationAudit 29, ContactsLeads 24, ContentSeo 39, Geography 53, IdentityAccess 37, ListingLifecycle 16, Media 34, ModerationReports 19, MonetizationPayments 40, Professionals 30, RealEstateCatalog 34, SearchDiscovery 30.

## Couverture qualitative existante

- Aggregate Roots et transitions métier ;
- Value Objects et politiques ;
- cas d’usage avec Registries/Catalogs fakes ;
- démarrage Foundation ;
- enveloppes physiques et indépendance du framework ;
- interdictions Laravel/Infrastructure dans Domain, Eloquent, SQL/accès base, Repository concret, dépendances inter-modules et dépendances inverses Application/Infrastructure.

## Couverture absente par conception

- contrats partagés Fake/Repository ;
- round-trip mapping complet ;
- PostgreSQL 18.x réel ;
- concurrence à deux connexions ;
- contraintes/réservations/rollback ;
- Unit of Work et atomicité Outbox ;
- ordre, reprise, quarantaine et idempotence Dispatcher ;
- restauration et exploitation.

Ces absences sont normales avant implémentation. Elles deviennent bloquantes dès qu’un adapter concret apparaît.

## Déterminisme

Les tests d’architecture trient les chemins, ne dépendent d’aucun service, base, réseau ou ordre d’exécution et analysent les sources PHP/token declarations directement. Le résultat de référence est vert : 19 tests, 10 033 assertions.
