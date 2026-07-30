<?php

use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyMapper;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyWriter;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

$items = [
    new PublicGeographyBreadcrumbItem('Accueil', 'https://appart.sn/'),
    new PublicGeographyBreadcrumbItem('Dakar', 'https://appart.sn/dakar'),
];
$payload = json_encode(
    ['locality' => 'Dakar', 'breadcrumb' => array_map(static fn ($item) => ['label' => $item->label, 'url' => $item->url], $items)],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
);
$revision = (new PublicGeographyRevisionStrategy)->revise(1, $payload, 'place:1:published');
$decision = new PublicGeographyDecision('place:dakar', $revision, 'Dakar', $items);

echo (new PostgreSqlPublicGeographyWriter(
    PostgreSqlTestEnvironment::connection(),
    new PostgreSqlPublicGeographyMapper,
))->store($decision)->value;
