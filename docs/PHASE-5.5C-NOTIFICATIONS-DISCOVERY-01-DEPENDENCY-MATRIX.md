# Notifications — Dependency Matrix

| Dépendance | Statut | Limite |
|---|---|---|
| Identity public | Autorisée ultérieurement | Compte/endpoints minimaux, aucune PII superflue |
| Moderation public | Autorisée ultérieurement | Fait confirmé uniquement |
| Reservations public | Autorisée ultérieurement | Fait confirmé uniquement |
| ContentSeo public | Conditionnelle | Décision certifiée, jamais Persistence |
| Search public | Conditionnelle | Frontière gelée, aucun internals Search |
| Transport externe | Sortante future | Remise technique seulement |
| Aggregate/Persistence cross-domain | Interdite | Violation d'ownership |
| Runtime Health | Interdite | Disponibilité non métier |
| Transaction distribuée | Interdite | Émission découplée |

Le flux autorisé est unidirectionnel : fait confirmé vers intent Notifications,
puis remise technique. Aucun retour ne modifie le domaine source.
