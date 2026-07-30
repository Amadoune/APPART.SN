# Phase 4.7C — Runtime Binding Matrix

| Dépendance | Source | Garantie |
|---|---|---|
| Workflow | 4.7A | instance unique, pure |
| Mapper | 4.7B | instance unique, mécanique |
| Canonicalizer | 4.7B-R2 | instance unique, déterministe |
| Transaction | 4.7B | instance unique, locale ou externe à l'exécution |
| Repository | 4.7B | instance unique |
| Store | alias du repository | identité d'instance |
| PDO | Runtime PostgreSQL existant | aucune connexion parallèle |

Sont absents du graphe : Aggregate historique, orchestration, contexte d'exécution, Event, Inbox, Outbox et HTTP.
