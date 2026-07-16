# APPART.SN REBUILD

Socle Foundation v1.0 d’APPART.SN, construit comme un monolithe modulaire sous PHP 8.5 et Laravel 13, avec PostgreSQL 18 comme plateforme de persistance cible.

Le Sprint J0 ne contient aucune fonctionnalité métier. Les treize domaines sont représentés uniquement par des enveloppes vides sous `src/Modules/`. Le domaine reste indépendant de Laravel ; les interfaces, adaptateurs et projections appartiennent à la périphérie applicative sous `app/`.

## Contrôles du socle

- `composer test` vérifie le démarrage du framework et les protections d’architecture.
- `composer test:architecture` vérifie les frontières physiques du socle.
- `composer quality` exécute le formatage en mode contrôle puis tous les tests.

La documentation normative se trouve sous `docs/`.
