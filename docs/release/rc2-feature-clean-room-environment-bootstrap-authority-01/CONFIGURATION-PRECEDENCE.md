# Configuration Precedence

Variables process > PHPUnit `<env>` > `.env` Laravel > fallbacks `config/database.php`. `phpunit.xml` fixe `APP_ENV=testing` mais aucun `APP_KEY`/`DB_*`. Une clean-room n'a pas `.env`; les fallbacks PostgreSQL deviennent host 127.0.0.1, port 5432, database laravel, username root, password vide.
