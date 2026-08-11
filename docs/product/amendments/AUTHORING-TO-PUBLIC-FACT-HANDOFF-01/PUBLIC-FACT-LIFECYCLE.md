# Public Fact Lifecycle

```text
Authoring draft mutable
        ↓ Submit
snapshot candidat canonique
        ↓ acceptation owner-scoped Listing Lifecycle
faits candidats figés
        ↓ BeginReview
inchangés
        ↓ ApproveAndPublish
transaction scellée dans la révision publiée
        ↓ rebuild
PublicListingReadModel
        ↓
PublicSearchListingSummary
```

## Invariants

- Le snapshot est complet avant la mutation Submit.
- `transactionKind` appartient au catalogue fermé `sale|rent` existant.
- Le checksum couvre listing id, version Authoring, transaction, intent et instant d'observation canonique.
- Un même intent et un même checksum convergent vers AlreadyApplied.
- Un même intent avec un payload différent diverge explicitement.
- La publication est refusée si les faits candidats sont absents ou incohérents pour un Listing créé après l'ouverture du contrat.
- Aucun builder ni Reader ne relit Authoring.
- Une modification ultérieure du draft ne réécrit jamais une publication existante ; elle exige un nouveau cycle explicite.

## Moment exact

Le fait devient public lors de la transition terminale `ApproveAndPublish`, au même instant autoritatif que `ListingPublished`. Submit fige seulement une candidate non publique.
