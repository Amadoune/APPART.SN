# External CI — Prerequisite Matrix

| Prérequis | État | Action requise |
|---|---|---|
| Repository distant officiel | MISSING | Autorité : désigner URL et owner |
| R5 présent à distance sans réécriture | BLOCKED | Publier/vérifier le tag après désignation |
| GitHub Actions autorisé | MISSING | Owner externe : confirmer disponibilité et politique |
| Runner `ubuntu-24.04` | PARTIAL | Déclaré dans le workflow, disponibilité réelle non prouvée |
| PHP 8.5.8 / Composer 2.9.4 | PARTIAL | Épinglés, installation externe non observée |
| Node 24.17.0 / npm 11.13.0 | PARTIAL | Épinglés, installation externe non observée |
| PostgreSQL 18.4 par digest | PARTIAL | Image épinglée, pull externe non observé |
| Permissions `contents: read` | PASS | Déclaration minimale présente |
| Identifiant permanent du run | BLOCKED | Disponible seulement après exécution externe |
| Logs et artefact conservables | PARTIAL | Workflow prévu, rétention 30 jours non éprouvée |
| Second environnement indépendant | MISSING | Désigner opérateur et environnement séparés |
