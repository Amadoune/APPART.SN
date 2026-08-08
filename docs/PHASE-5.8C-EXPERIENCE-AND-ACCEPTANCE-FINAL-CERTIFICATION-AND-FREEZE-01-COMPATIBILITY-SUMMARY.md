# Compatibility Summary

| Chaîne | Garantie gelée |
|---|---|
| OwnerSource vers Readers | 28 réductions mécaniques |
| Readers vers HTTP | mappings 200/404/503 |
| Readers vers Events | status et observedAt |
| Events vers Deliveries | EventType, status et observedAt |
| Deliveries vers Outbox | identité et checksum canoniques |
| Outbox vers Transport | sérialisation bijective |
| Transport vers Routing | sept destinations statiques |
| Routing vers Consumer | validation déterministe sans effet |

Aucun transfert d'autorité métier, aucun score UX et aucune mutation Consumer.

