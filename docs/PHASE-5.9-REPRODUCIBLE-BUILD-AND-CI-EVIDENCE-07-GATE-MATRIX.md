# Evidence 07 Gate Matrix

| Gate | Statut |
|---|---|
| Clone neuf / identité R5 | PASS — tag annoté exact, HEAD R5, source R4 ancêtre, worktree initial propre |
| Runtime pinning | PASS — PHP 8.5.8, Composer 2.9.4 + SHA-256, Node 24.17.0, npm 11.13.0 |
| Dependency Restore | FAIL — Composer install exit 1, ZIP et `unzip`/`7z` absents |
| Unit | BLOCKED |
| Feature | BLOCKED |
| Architecture | BLOCKED |
| Foundation | BLOCKED |
| PostgreSQL | BLOCKED |
| PHPStan | BLOCKED |
| Pint global | BLOCKED |
| Frontend | BLOCKED |
| Packaging A | BLOCKED |
| Packaging B | BLOCKED |
| Comparaison / manifeste / checksums | BLOCKED |
| Clean-room | BLOCKED |
| CI externe | MISSING |
| Reproduction indépendante | MISSING |

Toute porte postérieure à la première divergence sera classée `BLOCKED` ou `MISSING` conformément au fail-fast.
