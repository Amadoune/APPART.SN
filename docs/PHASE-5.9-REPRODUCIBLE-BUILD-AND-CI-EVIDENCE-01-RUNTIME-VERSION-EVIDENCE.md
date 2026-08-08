# Runtime Version Evidence

| Runtime/outillage | Version locale observée | Épinglage reproductible | Statut |
|---|---|---|---|
| PHP | 8.5.8 NTS, VS2022 x64 | composer.json autorise ^8.5, patch non épinglé | PARTIAL |
| Laravel | 13.20.0 installé | composer.lock | PASS |
| Composer | 2.9.4 | aucun fichier d'outil/image | PARTIAL |
| Node | 24.17.0 | aucun .nvmrc/.node-version | PARTIAL |
| npm | 11.13.0 | aucun packageManager dans package.json | PARTIAL |
| Vite | 8.1.5 installé | package-lock.json | PASS |
| PostgreSQL client | introuvable dans PATH/Laragon auditée | aucune version cible build | MISSING |
| OS | Windows 10.0.26200.8875 | aucune image clean-room | PARTIAL |

Les extensions PHP locales requises par les dépendances passent check-platform-reqs. La version de production cible reste hors périmètre et non démontrée.

