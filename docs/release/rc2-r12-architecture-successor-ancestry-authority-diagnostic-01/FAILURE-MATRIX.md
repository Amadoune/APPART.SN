# Architecture Failure Matrix

Légende : `F` = FAIL de baseline ; `E` = ERROR de bootstrap de test ; `C` = blob correctif présent dans le tag. R8/R9/R10 n'ont pas été réexécutés dans ce diagnostic.

| # | Test / dataset | Type et cause immédiate sur R5/F22/6ec7d1ec | R5 | F22 | 6ec7d1ec | R8 | R9 | R10 | First fixed commit |
|---:|---|---|---|---|---|---|---|---|---|
| 1 | `AccountStatusPersistenceArchitectureTest::test_persistence_does_not_decide_transitions_or_import_future_layers` | F — corpus trop large; détection de `Http` dans des persistences IdentityAccess étrangères à la slice testée | F | F | F | C | C | C | `ebef23e1` |
| 2 | `FoundationArchitectureTest::test_the_module_envelopes_exist` | F — `PublicationReview` existe mais manque à la liste des modules attendus | F | F | F | C | C | C | `ebef23e1` |
| 3 | `FoundationArchitectureTest::test_the_domain_has_no_framework_dependency` | F — le scan couvre tout `src`, y compris Infrastructure, au lieu de se limiter à `/Domain/` | F | F | F | C | C | C | `ebef23e1` |
| 4 | `InfrastructureBaselineArchitectureTest::test_production_code_contains_no_forbidden_persistence_implementation` / `database access` | F — regex textuelle confond des identifiants/mentions avec un accès DB; R8 passe à l'analyse tokenisée de `PDO` | F | F | F | C | C | C | `ebef23e1` |
| 5 | même méthode / `SQL` | F — faux positif `SELECT … FROM` dans du code non SQL; R8 inspecte uniquement les littéraux PHP | F | F | F | C | C | C | `ebef23e1` |
| 6 | `InfrastructureBaselineArchitectureTest::test_only_explicitly_authorized_concrete_repositories_exist` | F — `PostgreSqlPlaceRepository` absent du catalogue attendu | F | F | F | C | C | C | `ebef23e1` |
| 7 | `InfrastructureBaselineArchitectureTest::test_modules_do_not_import_other_modules` | F — dépendance PublicationReview→ListingLifecycle certifiée mais non admise | F | F | F | C | C | C | `ebef23e1` |
| 8 | `InfrastructureBaselineArchitectureTest::test_property_persistence_is_limited_to_its_authorized_slice` | F — slice `RealEstateCatalog/Infrastructure/Geography` non admise | F | F | F | C | C | C | `ebef23e1` |
| 9 | `InfrastructureBaselineArchitectureTest::test_media_persistence_is_confined_to_its_dedicated_slice` | F — slice `Media/Infrastructure/BinaryStorage` non admise | F | F | F | C | C | C | `ebef23e1` |
| 10 | `InfrastructureBaselineArchitectureTest::test_persistence_types_are_aggregate_specific_and_never_shared` | F — tout suffixe `Snapshot` est traité comme persistence, même hors `/Persistence/` | F | F | F | C | C | C | `ebef23e1` |
| 11 | `InfrastructureBaselineArchitectureTest::test_sql_and_pdo_are_confined_to_module_infrastructure` | F — détection textuelle SQL/PDO et slices applicatives certifiées incomplètes | F | F | F | C | C | C | `ebef23e1` |
| 12 | `InfrastructureBaselineArchitectureTest::test_sql_migrations_are_limited_to_the_authorized_postgresql_slices` | F — slice migrations `PublicationReview` absente et racine de scan trop large | F | F | F | C | C | C | `ebef23e1` |
| 13 | `ListingPublicationRuntimeCompositionArchitectureTest::test_existing_runtime_root_contains_one_lazy_production_graph` | F — provider gateway réel attendu comme liste vide | F | F | F | C | C | C | `ebef23e1` |
| 14 | `ListingPublicationRuntimeOrchestrationArchitectureTest::test_orchestration_foundation_contains_no_provider_outbox_or_projection_artifact` | F — même provider gateway réel attendu comme liste vide | F | F | F | C | C | C | `ebef23e1` |
| 15 | `ProjectionRebuildRuntimeSourceArchitectureTest::test_candidate_factory_delegates_to_certified_components_without_persistence_or_clock` | F — ancien nom `InspectablePublicListingProjectionSource` attendu au lieu de `CandidatePublicListingProjectionSource` | F | F | F | C | C | C | `ebef23e1` |
| 16 | `PublicGeographyV2ConsumerAlignmentArchitectureTest::test_v2_contracts_have_no_url_slug_or_search_dependency` | E — appel à `Container::path()` depuis un TestCase PHPUnit sans application Laravel | E | E | E | C | C | C | `ebef23e1` |
| 17 | `PublicGeographyV2ConsumerAlignmentArchitectureTest::test_alignment_adds_no_migration` | E — appel à `Container::databasePath()` dans le même contexte | E | E | E | C | C | C | `ebef23e1` |

Les 15 FAIL et 2 ERROR sont donc un ensemble de baseline unique, pas 17 régressions produit indépendantes.
