# APPART.SN Product Amendment — Public Transaction Promotion 01

## Question d'autorité

`transactionKind` devient sémantiquement public lorsque la publication du Listing est approuvée. Avant `ApproveAndPublish`, il demeure une intention d'Authoring modifiable ; après publication, il doit être un fait immuable de la version publique projetée.

Cette frontière sémantique est claire, mais aucun transport owner-scoped ne la matérialise actuellement.

## Traçage de la donnée

| Étape | Présence de transaction | Constat |
|---|---|---|
| `ListingDraftState` | oui, `transactionKind` | source persistée côté Authoring |
| `CreateListingDraftCommandV1` | non | la donnée n'entre pas dans Listing Lifecycle |
| `Listing::createDraft` | non | l'Aggregate ne la possède pas |
| `ListingDraftCreated` | non | aucun handoff événementiel |
| `SubmitListing` | non | transition sans transaction |
| `SendToReview` | non | transition sans transaction |
| `PublishListing` | non | aucune valeur à promouvoir |
| `ListingPublished` | non | événement terminal sans transaction |
| `ListingRegistry` | non | snapshot incapable de la restituer |
| `SearchListingProjectionBuilder` | non | ses trois sources ne la portent pas |
| `PublicListingReadModel` | non | fait public absent |
| `PublicSearchListingSummary` | non | exposition impossible |

## Comparaison des frontières

### A. Publication → Projection

Moment métier correct. Non implémentable actuellement : `PublishListing` et `ListingPublished` ne reçoivent pas la transaction.

### B. Listing Registry → Projection

Frontière technique cohérente avec le pipeline actuel, mais le Registry ne persiste pas la transaction. L'ajouter exigerait un changement du modèle Listing et une stratégie de compatibilité des snapshots existants.

### C. Projection Builder → Projection

Trop tard. Le builder ne peut ni inventer la valeur ni relire le draft Authoring. Aucun de ses inputs ne porte le fait.

### D. Handoff owner-scoped existant

Aucun handoff existant n'a été identifié. Le flux Authoring → publication transmet une demande de transition, pas le contenu public à figer.

## Cas P02

La donnée P02 a été créée directement via l'Aggregate Listing et ne possède aucun `transactionKind` dans sa source Lifecycle. La commande P02 n'alimente qu'une facette Search de type de bien. Ajouter ponctuellement `sale` modifierait P02 et inventerait une promotion sans contrat, deux opérations interdites.

## Décision

La promotion ne peut pas être matérialisée dans ce périmètre. Un futur amendement devra d'abord créer un handoff owner-scoped explicite au moment de la publication, définir la compatibilité des Listings historiques et autoriser séparément la reconstruction de la projection P02. Aucun de ces travaux n'est ouvert ici.
