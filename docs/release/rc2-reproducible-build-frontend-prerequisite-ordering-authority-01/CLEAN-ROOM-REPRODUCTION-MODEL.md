# Clean-Room Reproduction Model

Clean-room A et B doivent chacun exécuter indépendamment : checkout exact, restores, frontend build, suites, packaging.

Aucun `public/build`, manifest, `vendor`, `node_modules` ou artifact ne peut être copié de A vers B. Les artifacts finaux sont comparés seulement après leur production indépendante.
