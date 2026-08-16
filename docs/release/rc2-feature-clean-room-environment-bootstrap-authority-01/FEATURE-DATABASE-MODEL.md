# Feature Database Model

Modèle C : sous-familles. Les tests HTTP/composition utilisent la connexion Laravel PostgreSQL; au moins 78 fichiers Feature touchent PDO/DB et plusieurs utilisent explicitement `PostgreSqlTestEnvironment::migrate/reset`. L'autorité commune reste PostgreSQL `appart_test`, jamais SQLite par commodité.
