# Minimal Correction Boundary

Origine unique autoritative : `ebef23e12707d019eda7c1de799689ae470e1582`.

| Fichier | Blob actuel R5/F22/6ec7d1ec | Blob autoritatif R8/R9/R10 |
|---|---|---|
| `tests/Architecture/AccountStatusPersistenceArchitectureTest.php` | `61aedc37fd64c960c09dd3d002319f8482f3db19` | `6f9ddb6ad1a9ec39b63be2ac5dd9f6c05122bd6c` |
| `tests/Architecture/FoundationArchitectureTest.php` | `0f76f3e121ded9253ce3c132cd4939024160a23a` | `eedf407517433e37429218d2bcc3ac29c5f34cd6` |
| `tests/Architecture/InfrastructureBaselineArchitectureTest.php` | `95ff5d1462d99bec15d67c9f22b3e40157466fa6` | `aa452cd4ee56955b4a0543e747cc4f5580176736` |
| `tests/Architecture/ListingPublicationRuntimeCompositionArchitectureTest.php` | `92d3918cecae904003221bc546dbac502280bd4a` | `147e143fd839f103f2e5aeac9435bcbac03de0d7` |
| `tests/Architecture/ListingPublicationRuntimeOrchestrationArchitectureTest.php` | `46465e4e6b645a48f032c4ef15497e1863a8fa2f` | `e4c29e3d4b49c15dc40b546ec7934ad5bb86b480` |
| `tests/Architecture/ProjectionRebuildRuntimeSourceArchitectureTest.php` | `1337acb0d25fafa0e7677878b8ce0e9ccc81af4a` | `49e027e87ff737cec0a97f0a63b9c02dfb0e93dd` |
| `tests/Architecture/PublicGeographyV2ConsumerAlignmentArchitectureTest.php` | `07e9642d9fbb99b6e727ac47596e4bf27d7b5d2b` | `e29def2474ff19d58f0bf371b8479da695882e6c` |

La frontière est test-only et indivisible pour les 17 résultats observés : chaque fichier corrige au moins un résultat distinct. Elle exclut explicitement `BuildCiSourceIdentityArchitectureTest.php`, `.github/workflows`, `build/runtime.lock.json`, `tools/release/build-release.sh`, toute source applicative, toute migration, `phpunit.xml` et tout autre fichier R8.

La restauration éventuelle devra être blob par blob; aucun cherry-pick global de R8 n'est autorisé par ce diagnostic.
