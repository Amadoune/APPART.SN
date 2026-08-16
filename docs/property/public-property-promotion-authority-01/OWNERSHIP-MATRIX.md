# Ownership Matrix

| Ressource / décision | Owner actuel démontré | Peut lire Authoring ? | Peut créer/muter Aggregate Property ? | Ne doit pas faire |
|---|---|---:|---:|---|
| `PropertyAuthoringState` | RealEstateCatalog Authoring, scope propriétaire IAM | Oui, via `PropertyAuthoringStore` et surfaces owner-scoped | Non par ce contrat | Exposer l'owner depuis le client ou devenir projection publique |
| Listing Authoring / Lifecycle | Listing Lifecycle | Oui, uniquement via `PropertyAuthoringCatalogAdapter` pour disponibilité | Non | Déduire ou fabriquer le Property public |
| Media Runtime | Media | Oui, via adapter read-only owner-scoped pour reconnaître le Property | Non | Créer Aggregate Property ou stockage Property parallèle |
| Aggregate `RealEstateCatalog\Property` | RealEstateCatalog Domain | Aucun chemin actuel depuis Authoring | Oui, exclusivement via use cases Property et `PropertyRegistry` | Être synthétisé par Listing, Media ou Projection |
| `PropertyRegistry` | RealEstateCatalog Infrastructure | Non : il lit les tables Aggregate | Persiste uniquement l'Aggregate fourni par les use cases | Interpréter `PropertyAuthoringState` implicitement |
| Property Lifecycle | RealEstateCatalog Lifecycle | Non dans la composition observée | Non : il gère son workflow, distinct de l'Aggregate | Transformer un événement lifecycle en création Property |
| Public Projection | Public Projection Foundation | Non ; ses sources sont les registries/readers certifiés | Non | Relire Authoring, inventer des faits, contourner `PropertyRegistry` |
| Search / Public Listing | Consumers de Public Projection | Non | Non | Rejoindre Authoring ou filtrer sur des données non projetées |

## Responsabilités à préserver par toute future qualification

- IAM reste source exclusive de l'identité propriétaire.
- Authoring reste owner des intentions avant promotion.
- RealEstateCatalog Domain reste owner des invariants et de la création de l'Aggregate.
- Listing Lifecycle ne reçoit qu'une référence/éligibilité Property et reste owner du Listing.
- Media reste owner de ses assets/collections.
- Public Projection reste une dérivation read-only et idempotente.
- Search et Public Listing lisent uniquement la Projection publique.

## Limite de décision

L'architecture permet d'identifier les owners existants, mais ne désigne pas encore l'owner applicatif d'une future promotion. Le Discovery ne lui en assigne aucun.
