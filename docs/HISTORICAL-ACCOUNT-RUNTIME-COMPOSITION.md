# Historical Account — Runtime Composition

## Graphe

```text
AccountRegistry
→ PostgreSqlAccountRepository
→ PDO
→ AccountPersistenceMapper
```

Le port `AccountRegistry` est un alias du singleton
`PostgreSqlAccountRepository`. Le port et l'implémentation partagent donc
exactement la même instance.

`AccountPersistenceMapper` et `PostgreSqlAccountRepository` sont enregistrés
comme singletons paresseux. Aucun composant n'est résolu pendant
l'enregistrement du provider.

## Bindings

```text
AccountPersistenceMapper
→ singleton

PostgreSqlAccountRepository
→ singleton

AccountRegistry
→ alias unique de PostgreSqlAccountRepository
```

PDO reste fourni par la composition PostgreSQL commune. Le Repository et les
futurs participants transactionnels pourront partager cette même instance.

## Runtime Health

Le catalogue certifié demeure strictement inchangé à 55 capacités. La
composition Historical Account est vérifiée directement par ses tests de
composition et n'ajoute aucune capacité Runtime Health.

La sonde Runtime Health historique reste Healthy sans résoudre le nouveau
graphe, sans appeler `find`, `add` ou `save`, sans requête et sans transaction.

## Frontière

La composition ne modifie ni `AccountRegistry`, ni le Repository, ni Snapshot
V1, ni les migrations 041/042. Elle n'enregistre aucun Workflow Account Status
et ne reprend pas 4.9D.
