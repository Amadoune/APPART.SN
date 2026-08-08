# Notifications — Authority Analysis

| Responsabilité | Autorité |
|---|---|
| Préférences par catégorie et canal | `Notifications` |
| Adresse ou endpoint vérifié | `IdentityAccess` |
| Modèle, version, locale et variables admises | `Notifications` |
| Sélection et politique de canal | `Notifications` |
| Fait déclencheur | Domaine source |
| Identité d'émission et déduplication | `Notifications` |
| Retry, backoff, limite et terminalité | `Notifications` |
| Suppression, expiration et rétention | `Notifications` |
| Acceptation technique par un fournisseur | Adapter futur, sans autorité métier |

La fermeture d'un compte, l'invalidation d'un endpoint ou une interdiction légale
sont des faits publics entrants. Leur interprétation en suppression ou blocage de
remise appartient à la politique locale Notifications.
