# Phase 4.7G-R1 — Administrative Action Lifecycle Routing Runtime Composition

## Graphe

```text
AdministrativeActionLifecycleEventRouter
→ DurableAdministrativeActionLifecycleEventRouter
→ AdministrativeActionLifecycleInboxStore
→ PostgreSqlAdministrativeActionLifecycleInboxRepository
→ PDO PostgreSQL Runtime existant
```

Le serializer et la politique de consommation sont des singletons stateless internes.

## Garanties

* bindings et alias uniques ;
* instances partagées entre ports et implémentations ;
* réutilisation exclusive du PDO Runtime ;
* aucune lecture, écriture ou transaction au bootstrap ;
* aucun routage ou traitement au bootstrap ;
* aucun Fake, Null Object ou fallback.

## Runtime Health

Seuls les ports publics suivants sont exposés :

* `AdministrativeActionLifecycleInboxStore` ;
* `AdministrativeActionLifecycleEventRouter`.

Runtime Health passe de 48 à 50 capacités. Le repository, le routeur concret, le serializer et la politique demeurent des détails internes.
