# Final Version Model

Sans snapshot, le candidat est version 1. Pour un snapshot courant, le matérialiseur compare les trois révisions owner Listing/Search/Property.

Ensemble identique : même version. Ensemble dominant : version courante + 1. Ensemble inférieur : `RejectedObsolete`. Versions croisées, fait différent au même niveau ou payload logique divergent : `Divergent`.

Le writer reste l’autorité terminale : Applied, AlreadyApplied, RejectedObsolete ou Divergent.
