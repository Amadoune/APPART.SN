# Persistence Compatibility

La migration historique `002_listing.sql` impose `listing_lifecycle.listing_revisions.reason NOT NULL`. Elle n'est pas modifiée.

La migration additive `092_listing_transition_reason_optional.sql` supprime uniquement cette contrainte. Elle ne réécrit aucune ligne :

- les anciennes raisons restent byte-for-byte dans leur colonne ;
- les nouvelles révisions peuvent porter SQL `NULL` ;
- le mapper reconstruit `null` sans fabriquer de `TransitionReason` vide ;
- les raisons présentes continuent d'être validées par le Value Object.

Le rollback 092 restaure `NOT NULL`. Il refuse naturellement de s'exécuter si des lignes sans raison existent ; cette précondition évite toute perte ou substitution silencieuse de données.
