# Missing Data Decision

| Donnée manquante | Décision Blueprint | État préalable requis |
|---|---|---|
| Référence | Autorité RealEstateCatalog dédiée et déterministe, ou saisie Authoring certifiée ; aucune constante P02 | Décision/contrat futur certifié |
| Surface | Extension de Property Authoring | Champ versionné et owner-scoped |
| Rooms | Extension de Property Authoring | Champ versionné et owner-scoped |
| Bathrooms | Extension de Property Authoring | Champ versionné et owner-scoped |
| Construction year | Extension optionnelle Authoring | `null` explicitement permis par Domain |
| Structured address | Extension de Property Authoring | AddressLine/AddressId ou données permettant leur création sans invention |
| Geographic place | Sélection via autorité Geography existante | `GeographicPlaceId` persistant et utilisable |
| Business year | Autorité temporelle applicative explicitement contractée | Dérivation normative depuis `occurredAt` |

`city` et `neighborhood` actuels restent des libellés préparatoires. Ils ne sont pas une autorité Geography.

Jusqu'à matérialisation de ces sources, le seul comportement conforme est `IncompleteAuthoring`, sans mutation. Cette lacune interdit le GO d'implémentation.
