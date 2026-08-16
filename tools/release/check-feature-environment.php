<?php

namespace Appart\Release;

use PDO;
use Tests\Support\FeatureEnvironmentPreflight;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$names = ['APP_ENV', 'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APPART_APPLICATION_PG_DATABASE'];
$environment = [];
foreach ($names as $name) {
    $environment[$name] = getenv($name);
}

$database = FeatureEnvironmentPreflight::verify($environment, static function () use ($environment): string {
    $pdo = new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s', $environment['DB_HOST'], $environment['DB_PORT'], $environment['DB_DATABASE']),
        $environment['DB_USERNAME'],
        $environment['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );

    return (string) $pdo->query('SELECT current_database()')->fetchColumn();
});

fwrite(STDOUT, "Feature environment verified: {$database}\n");
