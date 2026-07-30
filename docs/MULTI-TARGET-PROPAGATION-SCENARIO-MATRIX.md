# Matrice des scénarios multi-cibles

| Situation | Plan | Garantie |
|---|---|---|
| zéro cible | `NoTargets` | aucun effet inventé |
| une cible | `Completed` | un traitement logique |
| plusieurs cibles | pages puis `Completed` | aucune cible ignorée |
| crash avant fin | redelivery depuis le message source | reprise sans perte |
| cible déjà appliquée | redelivery idempotente | aucun double effet |
| checkpoint invalide | `Corrupted` | aucun fallback |
| identité invalide | `InvalidIdentity` | aucun ciblage arbitraire |

L'ordre causal du message source reste celui de l'Outbox. À l'intérieur du message, les ListingId
sont traités dans l'ordre stable certifié par 3.9A.
