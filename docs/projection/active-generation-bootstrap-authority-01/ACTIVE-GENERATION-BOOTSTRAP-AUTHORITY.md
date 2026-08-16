# Active Generation Bootstrap Authority 01

## Décision normative

Le bootstrap initial est une opération explicite de **déploiement/exploitation Public Projection**, exécutée par un deployment operator. L'opérateur génère une fois un UUIDv4 cryptographiquement sûr, le consigne dans la preuve de déploiement et le fournit explicitement à la commande. Cette génération devient l'identité stable de tous les replays du même bootstrap.

Le bootstrap doit contenir au moins une projection certifiée: Candidate, rebuild du scope explicite, manifeste non vide, validation, activation. Aucun auto-bootstrap au démarrage, aucune migration de données, aucun SQL direct.

Verdict: **GO PROPOSÉ**. Le prochain chantier autorisé est `ACTIVE GENERATION BOOTSTRAP IMPLEMENTATION 01`.
