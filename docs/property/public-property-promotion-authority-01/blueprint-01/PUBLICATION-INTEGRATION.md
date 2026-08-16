# Publication Integration

Sémantique V1 retenue : **la promotion doit être terminée avant `Submitted`, donc nécessairement avant `Published`.**

Séquence :

1. Submit owner-scoped relit la version Authoring.
2. `PromoteAuthoredPropertyV1` retourne `Applied` ou `AlreadyApplied`.
3. Seulement alors, Listing passe à `Submitted` et émet `ListingSubmitted`.
4. BeginReview et ApprovePublication ne relisent pas Authoring et ne déclenchent pas la promotion.
5. Projection après Published trouve obligatoirement le Property dans `PropertyRegistry`.

Tout autre résultat ferme Submit et reste visible comme erreur de promotion. Published ne peut pas précéder la promotion avec un simple `NotReady`; cette option masquerait un défaut d'autorité jusqu'à Projection.
