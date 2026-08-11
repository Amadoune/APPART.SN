# Evidence 10 Gate Matrix

| # | Gate | Statut | Preuve terminale |
|---:|---|---|---|
| 1 | Clone / identité / propreté | PASS | Tag R5 annoté, commit exact, worktree propre |
| 2 | Runtime | PASS | PHP 8.5.8 ; Composer 2.9.4 ; Node 24.17.0 ; npm 11.13.0 |
| 3 | Composer validate | PASS | Validation terminale |
| 4 | Dependency Restore Composer | PASS | 112 packages |
| 5 | Dependency Restore npm | PASS | 58 packages |
| 6 | Unit | PASS | 2 872 tests ; 10 779 assertions |
| 7 | Feature | PASS | 339 tests ; 1 891 assertions |
| 8 | Architecture | PASS | 911 tests ; 85 843 assertions |
| 9 | Foundation | PASS | 1 test ; 4 assertions |
| 10 | PostgreSQL | PASS | 763 tests ; 3 623 assertions ; exit 0 |
| 11 | PHPStan | PASS | 0 erreur |
| 12 | Pint global | PASS | Résultat terminal |
| 13 | Frontend | PASS | Build terminal |
| 14 | Packaging A externe | PASS | Exit 0 ; 9 522 fichiers ; 970,146 s |
| 15 | Worktree propre après A | PASS | Aucun écart |
| 16 | Packaging B externe | PASS | Exit 0 ; 9 522 fichiers ; 964,935 s |
| 17 | Worktree propre après B | PASS | Aucun écart |
| 18 | SHA-256 archives | PASS | `d6f301796390b0c7da15fe19cb6fe8de0f53468e929645a310770924bec5cb3f` |
| 19 | SHA-256 arbres | PASS | `e19435b7bf7ed66f19dfdd7d3e33d0a5c937cefb22baab12dc42b6b6350514ab` |
| 20 | Manifeste final | PASS | R5, Composer et 9 522 fichiers consignés |
| 21 | Checksums finaux | PASS | Archives et arbres concordants |
| 22 | Clean-room | PASS | Répertoire vide, clone neuf R5, chaîne intégrale locale |
| 23 | CI externe | MISSING | Aucun remote Git ni exécuteur externe disponible |
| 24 | Reproduction indépendante | BLOCKED | Fail-fast après CI externe MISSING |

Première divergence : gate 23, `CI externe`.
