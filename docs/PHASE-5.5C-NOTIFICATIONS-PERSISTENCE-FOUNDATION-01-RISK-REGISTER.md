# Notifications Persistence — Risk Register

| Risque | Maîtrise |
|---|---|
| Double autorité | Journal Notifications unique |
| Conflit concurrent | Advisory lock et optimistic locking |
| Divergence journal/index | Mise à jour atomique, index dérivé |
| Répétition non déterministe | Checksum SHA-256 canonique |
| Couplage cross-domain | Aucun Aggregate ou FK externe |
| Fuite de PII | Clé sujet opaque, aucune donnée de contact |
| Rollback appelant compromis | Savepoint local |
