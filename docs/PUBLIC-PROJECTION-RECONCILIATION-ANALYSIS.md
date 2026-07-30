# Public Projection Reconciliation — analyse

## Audit

ADR-1009 définit la réconciliation comme défense secondaire. Les contrats existants fournissent les états Outbox, Cursor, progression, high-watermark et cinq scopes de replay. Aucun composant ne compare encore Outbox et projection, n'explique une divergence ou ne planifie une reprise bornée.

La réconciliation ne doit ni remplacer le chemin de livraison, ni reconstruire, ni modifier une projection. Elle consomme uniquement des observations préparées par un port et produit des demandes de replay autorisées.

## Décisions

- la source est paginée par checkpoint opaque et limite positive ;
- une observation porte progression, high-watermarks Outbox/projection, message attendu et blocages ;
- le détecteur produit MissingMessage, SequenceGap, HighWatermarkDivergence, BlockedBySourceReadiness ou BlockedBySequenceGap ;
- l'analyse associe une explication stable à chaque divergence ;
- MissingMessage cible le message connu, sinon une plage ;
- SequenceGap cible une plage ;
- HighWatermarkDivergence cible le high-watermark ;
- un blocage de séquence durable cible l'Aggregate ;
- SourceReadiness reste en attente sans replay automatique ;
- le planner enregistre les demandes mais ne les exécute jamais.
