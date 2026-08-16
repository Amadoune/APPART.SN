# Promotion Integration

Séquence cible :

1. PromoteAuthoredPropertyV1 valide owner, version et commande.
2. Il transmet son occurredAt stable à BusinessYearAuthorityV1.
3. L'autorité retourne BusinessYear `Resolved`.
4. L'orchestrateur transmet ce Value Object à RegisterProperty.
5. PropertyTypePolicy décide avec les autres faits.

L'autorité ne lit ni Authoring, ni Address, ni Geography, ni Projection/Search et ne décide aucun autre fait Property.
