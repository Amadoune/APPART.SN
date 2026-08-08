# Authority Analysis — Legacy Migration Owner Reader Boundary

`LegacyMigration` coordonne exclusivement l'observation des cinq streams. Cette coordination ne transfère aucun pouvoir métier : chaque owner cible reste seul habilité à qualifier ou modifier ses données.

`LegacyMigrationOwnerSource` constitue l'unique autorité technique d'entrée de la future adaptation. Les Owner Readers ne pourront que reproduire le statut source homonyme et l'instant d'observation public. Ils ne pourront ni interpréter une absence, ni calculer une readiness, ni rapprocher plusieurs streams.

| Sujet | Autorité |
|---|---|
| observation owner-scoped des cinq streams | `LegacyMigrationOwnerSource` |
| réduction vers les contrats V1 | futurs Owner Readers, mécanique seulement |
| décision métier cible | owner cible concerné exclusivement |
| arbitrage de migration ou cutover | hors frontière |

Aucune autorité n'est accordée au Runtime, à PostgreSQL, au mapper ou à l'Infrastructure.
