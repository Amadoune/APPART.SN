# PostgreSQL Naming Conventions

## Principe

Tous les noms sont spécifiques à un Aggregate et à son module. Les noms `Repository`, `Mapper`, `Snapshot`, `Transaction`, `Generic*`, `Base*`, `Abstract*`, `Shared*` ou équivalents sans Aggregate sont interdits pour les composants de persistance.

| Élément | Convention | Exemple certifié |
|---|---|---|
| Port applicatif | `<Aggregate>Registry` | `AdministrativeActionRegistry` |
| Repository PostgreSQL | `PostgreSql<Aggregate>Repository` | `PostgreSqlAdministrativeActionRepository` |
| Mapper | `<Aggregate>Mapper` | `AdministrativeActionMapper` |
| Snapshot Root | `<Aggregate>Snapshot` | `AdministrativeActionSnapshot` |
| Snapshot enfant | `<Child>Snapshot` | `ApprovalSnapshot` |
| Frontière transactionnelle | `<Aggregate>Transaction` | `AdministrativeActionTransaction` |
| Transaction PostgreSQL | `PostgreSql<Aggregate>Transaction` | `PostgreSqlAdministrativeActionTransaction` |
| Erreur d’intégrité | `Persistent<Aggregate>Integrity` | `PersistentAdministrativeActionIntegrity` |
| Contrat partagé | `<Aggregate>RegistryContract` | `AdministrativeActionRegistryContract` |
| Harness partagé | `<Aggregate>RegistryHarness` | `AdministrativeActionRegistryHarness` |
| Harness Fake | `Fake<Aggregate>RegistryHarness` | `FakeAdministrativeActionRegistryHarness` |
| Harness PostgreSQL | `PostgreSql<Aggregate>RegistryHarness` | `PostgreSqlAdministrativeActionRegistryHarness` |
| Entrée contractuelle | `<Backend><Aggregate>RegistryContractTest` | `PostgreSqlAdministrativeActionRegistryContractTest` |
| Test mapper | `<Aggregate>MapperTest` | `AdministrativeActionMapperTest` |
| Test intégration | `PostgreSql<Aggregate>RepositoryIntegrationTest` | `PostgreSqlAdministrativeActionRepositoryIntegrationTest` |
| Test concurrence | `PostgreSql<Aggregate>ConcurrencyTest` | `PostgreSqlAdministrativeActionConcurrencyTest` |
| Migration | `<NNN>_<aggregate_snake_case>.sql` | `001_administrative_action.sql` |

## Namespaces

- Port : `Appart\Modules\<Module>\Application\Contract`.
- Snapshots, mapper, transaction et intégrité : `Appart\Modules\<Module>\Infrastructure\Persistence`.
- Repository et transaction PostgreSQL : `Appart\Modules\<Module>\Infrastructure\Persistence\PostgreSql`.
- Contrats partagés : `Tests\Unit\Contracts\<Module>`.
- Tests mapper : `Tests\Unit\Infrastructure\<Module>`.
- Harness et tests PostgreSQL : `Tests\PostgreSQL\<Module>`.

Domain ne contient jamais `Repository`, `Mapper` ou `Snapshot`. Application peut définir uniquement le port métier nommé `Registry`; aucun type nommé `Repository` n’y est admis.

## SQL

- Schéma et tables : `snake_case`, vocabulaire métier du module, sans préfixe technique générique.
- Contraintes nommées et stables : `<table>_<colonnes>_<type>` où le type est `pk`, `fk`, `uq` ou `chk`.
- Index : `<table>_<colonnes>_idx`.
- Paramètres préparés : `snake_case` aligné sur le snapshot, sans concaténation dynamique de valeur.
- Une migration ne mélange jamais deux modules ou deux tranches non autorisées.
