# Version model

## Completion 01 — modèle final

La source revision est le vecteur canonique Listing + collection + attachment + assets. Une source nouvelle stable utilise version courante + 1 sous verrou; replay égal donne AlreadyApplied, source dominée RejectedObsolete et niveau égal divergent Divergent. Le détail est dans `FINAL-SOURCE-REVISION-MODEL.md`.

Le writer impose une version positive et monotone : absence puis version positive → Applied; même version/checksum/causation → AlreadyApplied; version inférieure → RejectedObsolete; même version divergente → Divergent.

La stratégie source exacte ne peut pas être définitivement fermée avant que la révision de delivery URL soit définie : une URL peut changer indépendamment de `MediaCollection::version()`.
