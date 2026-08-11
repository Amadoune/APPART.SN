# Implementation Evidence

Implémentation retenue :

- `TransitionEvidence`, `ListingRevision`, `ListingEvent` et snapshot acceptent `?TransitionReason` ;
- la policy autorise l'absence uniquement pour `SubmissionConfirmed`, `ReviewStarted` et `FavorableReview` ;
- la création Draft et toutes les autres transitions restent strictes ;
- le mapper et le repository PostgreSQL propagent `null` mécaniquement ;
- migration additive 092, sans modification de migration historique ;
- aucun changement Search, Public Projection, Registry Synchronization, Expiration ou Media.

Aucune chaîne factice et aucune donnée métier de substitution ne sont introduites.
