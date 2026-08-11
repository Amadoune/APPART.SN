# APPART.TEST LISTING PUBLICATION COMMAND CONTRACT 01 — Required Data Matrix

| Information | Owner | Source existante | Contrat | Disponible à la transition | Transformation | Statut |
|---|---|---|---|---|---|---|
| ListingRevisionId | Listing Lifecycle | fourni par le client uniquement à la création draft | `CreateListingDraftCommandV1` | non pour les transitions suivantes | aucune génération arbitraire | MISSING |
| Actor Submit | Identity / Listing owner | session IAM + ownership | attribut `iam_account_id`, authoring ownership | oui | conversion UUID typée | PASS |
| Actor Review/Publish | Moderation | décision validée | Moderation handoff | oui | conversion typée | PASS |
| Trigger | Listing Lifecycle | règles Domain internes | `TransitionTrigger` | non porté par le command workflow | mapping action→trigger non qualifié | PARTIAL |
| Reason | Listing Lifecycle / command authority | raison fixée à la création seulement | `TransitionEvidence` | non | interdite sans source | MISSING |
| Origin | Listing Lifecycle / command authority | origine fixée à la création seulement | `TransitionEvidence` | non | interdite sans source | MISSING |
| Timestamp | appelant / Moderation | request authoring ou event de décision | `occurredAt` | oui | UTC canonique | PASS |
| MediaCollectionId | Media owner | relation unique Property→collection | `MediaCollectionOwnershipLookup` | conditionnel | résolution owner-scoped | PARTIAL |
| ExpirationDate | Listing Lifecycle | aucune politique Application trouvée | `ExpirationDate` est seulement un VO Domain | non | calcul interdit | MISSING |
