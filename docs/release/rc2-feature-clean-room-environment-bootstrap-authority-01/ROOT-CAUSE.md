# Root Cause

Premier test observé : `PropertyAuthoringGeographySelectionHttpTest::test_workspace_exposes_hierarchical_selection_and_replay_context_without_authoring_persistence`. Le provider résout la connexion Laravel `pgsql`; faute de `DB_*`, `config/database.php` choisit `127.0.0.1:5432`, base `laravel`, utilisateur `root`, mot de passe vide. PDO échoue avant la logique Authoring et produit HTTP 500. IAM, Resume, Vite et fixtures ne sont pas causaux.
