# Command Contract

## PromoteAuthoredPropertyV1

Entrée minimale immuable :

| Champ | Sémantique |
|---|---|
| `propertyId` | Identité commune Authoring/Domain |
| `ownerAccountId` | Dérivé exclusivement de la session IAM |
| `commandId` | Identité idempotente fournie par l'orchestration Submit |
| `expectedAuthoringVersion` | Version exacte du snapshot que le propriétaire soumet |
| `occurredAt` | Instant métier stable de la première commande |

Les faits Property ne sont pas transportés. L'autorité relit `PropertyAuthoringStore` par `propertyId`, vérifie owner et version, puis construit les Value Objects uniquement depuis ce snapshot et les autorités explicitement qualifiées.

Le payload HTTP ne peut fournir ni référence, ni surface, ni rooms, ni bathrooms, ni adresse, ni place, ni business year. Une future extension de l'Authoring devra précéder l'implémentation du contrat.

Sortie : un résultat du catalogue fermé défini dans `RESULT-CATALOG.md`; aucune exception technique ne franchit la frontière applicative.
