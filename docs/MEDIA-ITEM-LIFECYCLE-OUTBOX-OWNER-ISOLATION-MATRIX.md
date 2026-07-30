# Media Item Lifecycle Outbox Owner Isolation Matrix

| Garantie | Preuve |
|---|---|
| écriture propriétaire | un message `Media` existe uniquement dans `media` |
| lecture propriétaire | le Reader restitue `sourceModule = Media` |
| absence de lecture croisée | une copie physique dans un autre owner n'est pas restituée comme Media |
| compatibilité structurelle | colonnes et contraintes identiques à l'owner historique de référence |
| coexistence | les sept autres owners restent vides lors d'une écriture Media |
| Inbox indépendante | `media_item_lifecycle_event_inbox` reste intacte et distincte |
