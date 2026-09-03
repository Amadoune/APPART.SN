# History Audit

Le commit correctif commun est `ebef23e12707d019eda7c1de799689ae470e1582`, parent `6e4f4997f9b82648ba7a3319f7462f638e66eeb6`, message `release: materialize RC2 R8 architecture correction`.

Il modifie onze fichiers : huit tests Architecture, le workflow, le runtime lock et le script de packaging. Parmi les huit tests, `BuildCiSourceIdentityArchitectureTest.php` ne fait que matérialiser l'identité R8. Les sept autres constituent les corrections des 17 non-PASS :

1. `AccountStatusPersistenceArchitectureTest.php` — borne le corpus à la persistence AccountStatus certifiée.
2. `FoundationArchitectureTest.php` — ajoute PublicationReview et limite l'interdiction framework au Domain.
3. `InfrastructureBaselineArchitectureTest.php` — tokenise les détections DB/SQL et admet les repositories, slices, migrations et dépendances certifiés.
4. `ListingPublicationRuntimeCompositionArchitectureTest.php` — attend l'unique provider gateway existant.
5. `ListingPublicationRuntimeOrchestrationArchitectureTest.php` — même alignement du provider.
6. `ProjectionRebuildRuntimeSourceArchitectureTest.php` — attend le nom de contrat courant.
7. `PublicGeographyV2ConsumerAlignmentArchitectureTest.php` — remplace les helpers Laravel indisponibles par des chemins déterministes depuis le dépôt.

Les changements workflow/runtime lock/packaging sont des changements d'identité de matérialisation R8 et ne sont pas nécessaires pour corriger les 17 résultats. Aucun changement applicatif, migration ou configuration PHPUnit n'est requis par ces corrections.

R9 et R10 ne modifient aucun des sept blobs. Aucun revert intentionnel n'existe dans leur ascendance.
