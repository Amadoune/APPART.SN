# Geography Authority

Geography possède les identités `PlaceId` et leur lifecycle. RealEstateCatalog expose `GeographicPlaceCatalog::statusOf`, qui valide qu'un `GeographicPlaceId` est `Usable`. Cette surface valide une identité connue ; elle ne transforme pas un libellé en identité.

Décision cible : le propriétaire sélectionne pendant Authoring un `GeographicPlaceId` provenant d'un catalogue Geography autoritatif. Property Authoring stocke cette identité avec sa version. Au moment de `RegisterProperty`, RealEstateCatalog revalide son statut.

`city` et `neighborhood` restent des libellés UX. Ils peuvent accompagner l'affichage, mais ne sont ni clés, ni preuve d'existence, ni source de hiérarchie. Aucun fuzzy matching, slug implicite ou création de place n'est permis.

Blocage actuel : aucune surface de sélection/résolution Geography owner-facing n'est qualifiée dans le périmètre audité. L'owner est déterminé, mais le mode d'acquisition exécutable reste absent.
