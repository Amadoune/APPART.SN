# Identity Strategy

| Stratégie | Replay | Persistance | Verdict |
|---|---|---|---|
| UUID aléatoire | Instable sans stockage | Ledger requis | Rejetée |
| UUID déterministe d'intention | Stable par construction | Aucune | **Retenue** |
| Séquence/réservation | Stable mais stateful | Réservation transactionnelle | Rejetée |
| Autorité avec ledger | Stable, mais complexité inutile | Ledger/migration | Rejetée |

Algorithme V1 : UUIDv5 RFC 4122, namespace URL standard `6ba7b811-9dad-11d1-80b4-00c04fd430c8`, nom canonique UTF-8 :

`https://appart.sn/real-estate-catalog/address-intents/v1/{propertyId}/{addressIntentId}`

Les UUID d'entrée sont canoniques minuscules. L'algorithme ne dépend ni du temps ni des faits Address.
