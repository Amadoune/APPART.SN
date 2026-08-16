# Preuve UI NotReady

`DeterministicPublicationReviewExperience::approve()` appelle Approve puis Projection. Lorsque Projection retourne `NotReady`, le résultat d'expérience est `PublicationReviewExperienceStatus::NotReady`.

`PublicationReviewExperienceController::render()` transforme en exceptions uniquement `Forbidden`, `NotFound`, `Conflict` et `DependencyUnavailable`. `NotReady` tombe dans la branche par défaut, puis `approve()` impose le mode de vue `confirmed`.

La Blade affiche alors « Publication confirmée » et « La projection a été activée » malgré :

- résultat fermé `not_ready` ;
- ledger Projection absent ;
- Projection Store vide.

Frontière responsable : **adaptation HTTP/UI de `PublicationReviewExperienceStatus::NotReady`**. Ce défaut est séparé de la cause `SearchMissing` et n'est pas corrigé ici.
