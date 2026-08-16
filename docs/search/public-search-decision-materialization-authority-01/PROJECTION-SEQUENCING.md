# Projection Sequencing

Le séquencement requis demeure :

`Published → SearchDecision Applied/AlreadyApplied → SearchDecisionReader Found → ProjectPublishedListingV1`.

Option A est la cible normative : Projection n'est autorisée qu'après succès SearchDecision. Option B reste nécessaire comme mécanisme de rattrapage/retry asynchrone, sans changer l'ordre de dépendance.

Les deux opérations restent dans des transactions owner-locales séparées. En cas de SearchDecision non prête, Projection conserve `NotReady`, sans fallback.

Cette séquence ne peut pas être implémentée avant qualification Rank/facets/identity/version.

## Completion 01

La qualification est désormais fermée. `Applied` ou `AlreadyApplied` doit être suivi d'un `SearchDecisionReader::Found` avant replay Projection. Missing, Corrupted, SourceMissing ou DependencyUnavailable conservent Projection `NotReady`, sans fallback.
