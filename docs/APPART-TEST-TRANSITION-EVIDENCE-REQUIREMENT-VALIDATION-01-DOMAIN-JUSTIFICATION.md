# Transition Evidence Requirement Validation 01 — Domain Justification

`ListingTransitionPolicy` vérifie les couples état/trigger/origin et les faits d'éligibilité. Elle ne lit jamais `TransitionReason`.

`SubmitListing`, `SendToReview` et `PublishListing` transmettent l'evidence à l'Aggregate sans interpréter le reason. L'Aggregate le copie dans `ListingRevision` et les événements sans branche conditionnelle.

`TransitionReason` valide seulement un texte de 3 à 500 caractères. Cette contrainte prouve une exigence de forme, pas une nécessité métier. Aucun catalogue, code sémantique ou règle de décision ne lui est associé.

Qualification transverse : trace d'audit/documentaire techniquement obligatoire, mais non invariant métier démontré pour les trois transitions étudiées.
