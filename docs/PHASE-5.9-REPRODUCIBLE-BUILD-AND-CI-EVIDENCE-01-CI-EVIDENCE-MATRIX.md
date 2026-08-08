# CI Evidence Matrix

| Gate CI attendue | État observé | Statut | Exigence minimale future |
|---|---|---|---|
| checkout SHA immuable | aucun workflow | MISSING | checkout par SHA, shallow ambigu interdit |
| versions runtime | locales seulement | PARTIAL | versions épinglées dans l'image/runner |
| Composer install | script absent | MISSING | install depuis lock, sans interaction |
| npm ci | script absent | MISSING | ci depuis lockfile v3 |
| build | npm run build local | PARTIAL | exécution clean-room |
| Unit/Feature/Architecture | scripts Composer disponibles | PARTIAL | sorties archivées liées au SHA |
| PostgreSQL | config dédiée présente | PARTIAL | service versionné et résultat archivé |
| PHPStan/Pint | scripts disponibles | PARTIAL | sorties terminales archivées |
| audit dépendances | composer audit script seulement | PARTIAL | PHP et JS, résultats datés |
| secret scan | absent | MISSING | scan source et artefact |
| manifeste | absent | MISSING | génération après toutes les gates |
| artefact | absent | MISSING | paquet immutable et checksum |
| rétention | absente | MISSING | politique, accès, durée |

Aucun fichier CI n'a été créé : sa matérialisation constitue une action technique future qui requiert autorisation après sélection d'un candidat propre.

