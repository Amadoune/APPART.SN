# SEO Listing Projection Analysis — Sprint 3.3B

## Source unique

`ListingSeoDecision` est l'unique source autorisée. Le builder n'a besoin ni des Aggregates Property, Listing ou MediaCollection, ni des Catalogs ayant servi à produire la décision. Toutes les règles ont déjà été appliquées par `ListingSeoDecisionPolicy` et validées par la factory de décision.

## Audit des champs

| Champ de décision | Copie dans la projection | Optionnel |
|---|---|---:|
| `listingId` | chaîne identitaire | non |
| `canonical` | URL normalisée exacte | non |
| `canonicalHistory` | URL, disposition, dates et cible de chaque entrée | non |
| `headline` | texte public exact | oui pour une page noindex |
| `description` | texte public exact | oui pour une page noindex |
| `indexability` | valeur `indexable|not_indexable` | non |
| `robots` | valeur `index_follow|noindex_follow` | non |
| `breadcrumb` | labels et URL déjà composés | peut être vide en noindex |
| `structuredData` | type et faits déjà validés | oui en noindex |
| `publicMedia` | URL publique exacte | oui en noindex |
| `publishedAt` | date immutable | oui en noindex |
| `expiresAt` | date immutable | oui en noindex |
| `decidedAt` | date immutable | non |
| `expiredTreatment` | traitement expiré exact | non |

`pageTreatment`, ajouté au modèle de décision, pilote uniquement l'existence de la projection. Il était nécessaire parce que `NotIndexable` ne permettait pas de distinguer une page conservée en noindex d'une page supprimée. Cette décision reste dans ContentSeo; le builder ne l'infère pas.

## Invariants déjà garantis

La factory `ListingSeoDecision::decide` garantit notamment :

- canonical courante présente dans l'historique;
- décision indexable associée à `index,follow`, page conservée, contenu, breadcrumb, structured data, média et dates complets;
- décision non indexable associée à `noindex,follow`;
- traitement expiré `Remove` associé à une page supprimée;
- traitement expiré `RetainNoIndex` associé à une page conservée et non indexable.

Le builder n'a donc aucune policy à réexécuter.

## Règles d'existence

- `pageTreatment=Remove` : aucune page et résultat `null`, quel que soit le motif déjà décidé.
- `pageTreatment=Retain` avec décision indexable : projection publique indexable.
- `pageTreatment=Retain` avec décision non indexable : projection publique conservée avec robots noindex déjà décidés.
- Listing expiré + `Remove` : `null`.
- Listing expiré + `RetainNoIndex` : projection non indexable.

## Reconstruction

La transformation est une copie déterministe de Value Objects ContentSeo vers des scalaires, tableaux et `DateTimeImmutable`. Elle ne génère aucun identifiant, URL, date, fallback ou contenu. Supprimer puis reconstruire depuis la même décision produit un objet égal sans perte d'information utile à la future page.
