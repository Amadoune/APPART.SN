# Property Authoring Integration

1. Property Authoring demande un niveau structuré au `GeographySelectionReaderV1`.
2. Il affiche les labels certifiés.
3. L'utilisateur choisit un item existant.
4. Authoring transporte et persiste `placeId` dans `PropertyAuthoringState` avec sa propre version.
5. Le replay Property utilise exactement cet ID.
6. Au moment de la promotion, RealEstateCatalog appelle `GeographicPlaceCatalog::statusOf`.

Authoring ne stocke pas l'Aggregate, les aliases, coordonnées ou breadcrumb. Aucune revision Geography n'est conservée en V1 : aucune règle existante ne l'exige, et la revalidation du statut courant est normative.

Un label modifié n'altère pas l'identité stockée. Une Place devenue disabled ou merged fait échouer la revalidation ; Authoring doit demander une nouvelle sélection explicite.
