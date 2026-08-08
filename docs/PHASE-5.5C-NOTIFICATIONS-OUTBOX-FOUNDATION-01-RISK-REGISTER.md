# Notifications Outbox Risk Register

| Risque | Maîtrise |
|---|---|
| Double insertion concurrente | messageId déterministe et PK |
| Divergence silencieuse | Checksum comparé, DivergentMessage explicite |
| Retry infini | Borne SQL et filtre à 10 |
| Rollback externe cassé | Savepoint local |
| Désordre de remise | Ordre stable créé/id |
| Couplage transport | Aucun Provider, Transport, Routing ou Consumer |
