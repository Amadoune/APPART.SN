# Geography Selection Verdict

**MISSING**

Geography possède l'Aggregate, les identités et une lecture par ID. RealEstateCatalog sait vérifier le statut d'un ID fourni. Il manque toutefois la première capacité indispensable : une surface certifiée read-only permettant au propriétaire d'obtenir une collection déterministe de Places sélectionnables et d'en choisir explicitement l'identité.

Le reader Public Geography n'est pas réutilisable pour ce besoin : il exige déjà `placeId`, appartient à la source de Projection et ne distingue pas une indisponibilité de dépendance dans son catalogue actuel (`Found`, `Missing`, `Corrupted`).

Aucun ID ne peut être fabriqué depuis city/neighborhood, slug ou fuzzy matching.
