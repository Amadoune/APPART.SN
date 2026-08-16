# Feature Preflight Implementation

`tools/release/check-feature-environment.php` appelle `Tests\Support\FeatureEnvironmentPreflight`. Il exige toutes les variables, refuse root, se connecte réellement, exige `current_database() = appart_test` et réutilise `PostgreSqlTestEnvironment::assertDatabaseNamesAreIsolated`.
