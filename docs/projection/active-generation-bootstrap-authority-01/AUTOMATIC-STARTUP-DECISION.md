# Automatic Startup Decision

Décision: **interdit**. Le démarrage applicatif ne crée ni Candidate ni Active. Une absence reste `ActiveGenerationMissing` jusqu'à l'opération explicite. Cela préserve auditabilité, contrôle du scope, validation du manifeste et fail-closed; un démarrage concurrent ne devient jamais une décision de déploiement implicite.
