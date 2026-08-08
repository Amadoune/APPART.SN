# Compatibility Matrix — Legacy Migration Outbox

| Surface | Autorisé | Interdit | Statut |
|---|---|---|---|
| Application Outbox | cinq Deliveries V1 | Events, Readers, Runtime, HTTP | Compatible |
| Repository | ports Outbox, Policy, PDO | Provider, Transport, Consumer | Enclave PostgreSQL |
| migration 085 | table et index Outbox dans `legacy_migration` | modification de 084 | Additive |
| lecture | ordre et retry borné | décision métier | Compatible |

Les Foundations antérieures restent fermées et inchangées. Les capacités 5.1 à 5.6 et la migration 084 demeurent gelées. Aucune Foundation ultérieure n'est ouverte.
