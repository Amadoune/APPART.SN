# Risk Register

| Risque | Maîtrise |
|---|---|
| duplication d'une autorité source | décisions limitées à AdministrationConsole |
| divergence journal/index | journal autoritatif, mise à jour atomique de l'index |
| concurrence | advisory lock owner-local et révisions monotones |
| rejeu divergent | checksum canonique et DivergentRevision |
| fuite inter-domaines | aucune FK ni dépendance cross-domain |
| rollback externe cassé | savepoints locaux et transaction appelante préservée |
