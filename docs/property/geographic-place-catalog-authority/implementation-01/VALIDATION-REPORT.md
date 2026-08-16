# Validation Report

Runtime utilisé : PHP Laragon 8.5.8 NTS.

| Campagne | Résultat | Tests | Assertions |
|---|---:|---:|---:|
| Unit policy/adapter + Feature binding + Architecture Catalog | PASS | 19 | 38 |
| PostgreSQL Catalog et composition Register/ChangeAddress | PASS | 2 | 8 |
| Régression RealEstateCatalog Property + F0 Architecture/Binding | PASS | 39 | 75 |
| Régression PostgreSQL F0 PlaceRepository | PASS | 5 | 17 |
| Architecture Catalog finale, scan Geography récursif | PASS | 2 | 117 |

Commandes ciblées exécutées :

- `artisan test tests/Unit/GeographicPlaceCatalog tests/Feature/GeographicPlaceCatalogBindingTest.php tests/Architecture/GeographicPlaceCatalogArchitectureTest.php` ;
- `phpunit --configuration phpunit.postgresql.xml tests/PostgreSQL/GeographicPlaceCatalog/PostgreSqlGeographicPlaceCatalogTest.php` ;
- `artisan test tests/Unit/Modules/RealEstateCatalog/PropertyUseCasesTest.php tests/Unit/Modules/RealEstateCatalog/PropertyTest.php tests/Architecture/GeographyPlacePersistenceArchitectureTest.php tests/Feature/GeographyPlacePersistenceBindingTest.php` ;
- `phpunit --configuration phpunit.postgresql.xml tests/PostgreSQL/GeographyPlacePersistence/PostgreSqlPlaceRepositoryTest.php`.

Qualité :

- PHPStan global : PASS, 0 erreur ;
- Pint ciblé : PASS après normalisation de l’ordre d’import du Provider registry ;
- `git diff --check` : PASS.

Aucune campagne globale de tests n’a été relancée ; seules les surfaces impactées et leurs régressions F0/RealEstateCatalog ont été exécutées.
