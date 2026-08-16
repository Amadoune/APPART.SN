# Final mutation refresh

| Mutation | Refresh |
|---|---|
| attach/MediaAdded | oui |
| detach/MediaRemoved/archive | oui |
| reorder | oui |
| primary change | oui |
| asset ready/version | oui |
| asset rejected/purged/unavailable | oui |
| caption seule | non en V2 |
| Listing Published | initial |
| dépublication/suspension/expiration | oui |

Les orchestrateurs admettent atomiquement `media.reconstruction.requested` dans l'outbox Media existante. Le consumer résout le Listing et appelle le même `MaterializePublicMediaDecisionV2`; aucun moteur refresh distinct.
