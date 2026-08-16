# Rapport de compatibilité

## Autorités préservées

F7 ne modifie aucun code. F0/F1, F2, F3, F4/F4-A, F5-A, `RegisterProperty`, `ChangeAddress`, `PropertyTypePolicy` et `GeographicPlaceCatalog` restent inchangés.

## Frontières

La Promotion demeure dans RealEstateCatalog Application, lit Authoring par port, utilise une transaction locale et ne dépend ni de Search ni de Projection.

## Compatibilité non certifiable

La frontière post-Promotion avec Listing présente une incohérence transactionnelle potentielle sur résultat fermé non réussi. Le défaut ne remet pas en cause l’atomicité propre de F6, mais empêche la recertification end-to-end requise avant RC2.
