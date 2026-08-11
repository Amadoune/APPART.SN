# Gateway Boundary Audit

## Données exigées par les use cases

| Use case | Entrées requises | Écriture |
|---|---|---|
| `SendToReview` | ListingId, ListingRevisionId, TransitionEvidence | Aggregate puis ListingRegistry avec optimistic locking |
| `PublishListing` | ListingId, MediaCollectionId, ListingRevisionId, ExpirationDate, TransitionEvidence | Aggregate, ListingRegistry et scellement des Public Facts |
| workflow BeginReview | ListingId, action, expected publication version | journal du workflow et événements |
| workflow ApproveAndPublish | ListingId, action, expected publication version | journal du workflow et événements |

## Frontière retenue

La Gateway se situe dans l'Application Listing Lifecycle. Elle compose des ports owner-scoped ; elle ne contient ni SQL, ni règle de transition, ni calcul d'éligibilité.

PublicationReview ne peut pas appeler séparément workflow et Aggregate : un crash intermédiaire recréerait une divergence. La Gateway appelle les deux sous une même autorité transactionnelle.

## Cohérence d'états

| Commande | Paire préalable obligatoire | Paire finale |
|---|---|---|
| BeginReview | workflow `Submitted` + Aggregate `Submitted` | workflow `UnderReview` + Aggregate `UnderReview` |
| ApproveAndPublish | workflow `UnderReview` + Aggregate `UnderReview` | workflow `Published` + Aggregate `Published` |

Une paire différente produit un résultat fermé de conflit/corruption ; aucun côté n'est « réparé » implicitement.

## Exclusions

- la Gateway ne possède pas PublicationReview Queue ;
- elle ne décide pas des permissions IAM ;
- elle ne lit ni Search ni Public Projection ;
- elle ne modifie pas Property ou Media ;
- elle ne déclenche pas P08.
